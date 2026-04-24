<?php

namespace App\Http\Controllers;

use App\Models\MenuHarian;
use App\Models\MenuDetailBahan;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class MenuHarianController extends Controller
{
    private function checkPengelola()
    {
        if (Auth::user()->role !== 'pengelola') {
            abort(403, 'Hanya pengelola yang dapat mengelola menu harian.');
        }
    }

    private function authorizeUnit(MenuHarian $menuHarian)
    {
        $user = Auth::user();
        if ($user->role === 'pengelola' && $menuHarian->unit_sppg !== $user->unit_sppg) {
            abort(403);
        }
    }

    public function index(Request $request)
    {
        $user = Auth::user();
        $query = MenuHarian::with('detailBahans.bahanPangan')
            ->where('unit_sppg', $user->unit_sppg)
            ->orderByDesc('tanggal');

        if ($request->filled('bulan')) {
            $query->whereMonth('tanggal', date('m', strtotime($request->bulan)))
                  ->whereYear('tanggal', date('Y', strtotime($request->bulan)));
        }

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        $menus = $query->paginate(15)->withQueryString();
        return view('menu-harian.index', compact('menus'));
    }

    public function create()
    {
        $this->checkPengelola();
        $user = Auth::user();
        $today = today()->format('Y-m-d');
        $existing = MenuHarian::where('unit_sppg', $user->unit_sppg)
            ->whereDate('tanggal', $today)->first();

        return view('menu-harian.create', compact('user', 'existing'));
    }

    public function store(Request $request)
    {
        $this->checkPengelola();
        $user = Auth::user();

        $request->validate([
            'tanggal'                      => 'required|date',
            'nama_menu'                    => 'nullable|string|max:255',
            'catatan'                      => 'nullable|string|max:500',
            'bahans'                       => 'required|array|min:1',
            'bahans.*.bahan_pangan_id'     => 'required|exists:bahan_pangans,id',
            'bahans.*.jumlah_gram'         => 'required|numeric|min:1',
            'bahans.*.jumlah_porsi'        => 'required|integer|min:1',
        ]);

        $exists = MenuHarian::where('unit_sppg', $user->unit_sppg)
            ->whereDate('tanggal', $request->tanggal)->exists();

        if ($exists) {
            return back()->withErrors(['tanggal' => 'Menu untuk tanggal ini sudah ada.'])->withInput();
        }

        DB::transaction(function () use ($request, $user) {
            $menuHarian = MenuHarian::create([
                'tanggal'   => $request->tanggal,
                'user_id'   => $user->id,
                'unit_sppg' => $user->unit_sppg,
                'nama_menu' => $request->nama_menu,
                'status'    => $request->input('status', 'draft'),
                'catatan'   => $request->catatan,
            ]);

            foreach ($request->bahans as $bahan) {
                MenuDetailBahan::create([
                    'menu_harian_id'  => $menuHarian->id,
                    'bahan_pangan_id' => $bahan['bahan_pangan_id'],
                    'jumlah_gram'     => $bahan['jumlah_gram'],
                    'jumlah_porsi'    => $bahan['jumlah_porsi'],
                ]);
            }
        });

        return redirect()->route('menu-harian.index')
            ->with('success', 'Menu harian berhasil disimpan.');
    }

    public function show(MenuHarian $menuHarian)
    {
        $this->authorizeUnit($menuHarian);
        $menuHarian->load('detailBahans.bahanPangan');
        return view('menu-harian.show', compact('menuHarian'));
    }

    public function edit(MenuHarian $menuHarian)
    {
        $this->checkPengelola();
        $this->authorizeUnit($menuHarian);

        if ($menuHarian->status === 'final') {
            return back()->with('error', 'Menu yang sudah final tidak dapat diedit.');
        }

        $menuHarian->load('detailBahans.bahanPangan');

        // ✅ Siapkan data existing di controller, bukan di Blade
        $existingBahans = $menuHarian->detailBahans->map(function ($d) {
            return [
                'id'           => $d->bahanPangan->id,
                'kode'         => $d->bahanPangan->kode,
                'nama_bahan'   => $d->bahanPangan->nama_bahan,
                'kategori'     => $d->bahanPangan->kategori,
                'energi'       => $d->bahanPangan->energi   ?? 0,
                'protein'      => $d->bahanPangan->protein  ?? 0,
                'lemak'        => $d->bahanPangan->lemak    ?? 0,
                'karbohidrat'  => $d->bahanPangan->karbohidrat ?? 0,
                'bdd'          => $d->bahanPangan->bdd      ?? 100,
                'jumlah_gram'  => $d->jumlah_gram,
                'jumlah_porsi' => $d->jumlah_porsi,
            ];
        })->values();

        return view('menu-harian.edit', compact('menuHarian', 'existingBahans'));
    }

    public function update(Request $request, MenuHarian $menuHarian)
    {
        $this->checkPengelola();
        $this->authorizeUnit($menuHarian);

        if ($menuHarian->status === 'final') {
            return back()->with('error', 'Menu yang sudah final tidak dapat diedit.');
        }

        $request->validate([
            'nama_menu'                    => 'nullable|string|max:255',
            'catatan'                      => 'nullable|string|max:500',
            'bahans'                       => 'required|array|min:1',
            'bahans.*.bahan_pangan_id'     => 'required|exists:bahan_pangans,id',
            'bahans.*.jumlah_gram'         => 'required|numeric|min:1',
            'bahans.*.jumlah_porsi'        => 'required|integer|min:1',
        ]);

        DB::transaction(function () use ($request, $menuHarian) {
            $menuHarian->update([
                'nama_menu' => $request->nama_menu,
                'catatan'   => $request->catatan,
                'status'    => $request->input('status', $menuHarian->status),
            ]);

            // Hapus detail lama, insert ulang
            $menuHarian->detailBahans()->delete();

            foreach ($request->bahans as $bahan) {
                MenuDetailBahan::create([
                    'menu_harian_id'  => $menuHarian->id,
                    'bahan_pangan_id' => $bahan['bahan_pangan_id'],
                    'jumlah_gram'     => $bahan['jumlah_gram'],
                    'jumlah_porsi'    => $bahan['jumlah_porsi'],
                ]);
            }
        });

        return redirect()->route('menu-harian.show', $menuHarian)
            ->with('success', 'Menu harian berhasil diperbarui.');
    }

    public function destroy(MenuHarian $menuHarian)
    {
        $this->checkPengelola();
        $this->authorizeUnit($menuHarian);

        if ($menuHarian->status === 'final') {
            return back()->with('error', 'Menu yang sudah final tidak dapat dihapus.');
        }

        $menuHarian->detailBahans()->delete();
        $menuHarian->delete();

        return redirect()->route('menu-harian.index')
            ->with('success', 'Menu harian berhasil dihapus.');
    }

    public function finalize(MenuHarian $menuHarian)
    {
        $this->checkPengelola();
        $this->authorizeUnit($menuHarian);
        $menuHarian->update(['status' => 'final']);
        return back()->with('success', 'Menu berhasil di-finalisasi.');
    }
}