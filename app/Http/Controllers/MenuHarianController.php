<?php

namespace App\Http\Controllers;

use App\Models\MenuHarian;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class MenuHarianController extends Controller
{
    public function index(Request $request)
    {
        $query = MenuHarian::with('detailBahans')
            ->orderByDesc('tanggal');

        if ($request->filled('bulan')) {
            [$tahun, $bln] = explode('-', $request->bulan);
            $query->whereYear('tanggal', $tahun)->whereMonth('tanggal', $bln);
        }

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        $menus = $query->paginate(15)->withQueryString();

        return view('menu-harian.index', compact('menus'));
    }

    public function create()
    {
        return redirect()->route('simulasi.index')
            ->with('info', 'Untuk membuat menu baru, gunakan fitur Simulasi Menu.');
    }

    public function store(Request $request)
    {
        return redirect()->route('simulasi.index')
            ->with('info', 'Untuk membuat menu baru, gunakan fitur Simulasi Menu.');
    }

    public function show(MenuHarian $menuHarian)
    {
        $this->authorizeUnit($menuHarian);
        $menuHarian->load('detailBahans.bahanPangan');
        return view('menu-harian.show', compact('menuHarian'));
    }

    public function edit(MenuHarian $menuHarian)
    {
        // Cek akses
        if (auth()->user()->role !== 'pengelola') {
            abort(403);
        }

        // Cek status
        if ($menuHarian->status === 'final') {
            return redirect()->route('menu-harian.show', $menuHarian)
                ->with('error', 'Menu sudah final, tidak bisa diedit.');
        }

        $menuHarian->load('detailBahans.bahanPangan');

        // Siapkan data existing bahans untuk JS prefill
        $existingBahans = $menuHarian->detailBahans
            ->filter(fn($d) => $d->bahanPangan)
            ->map(fn($d) => [
                'id'           => $d->bahanPangan->id,
                'kode'         => $d->bahanPangan->kode,
                'nama_bahan'   => $d->bahanPangan->nama_bahan,
                'kategori'     => $d->bahanPangan->kategori,
                'energi'       => $d->bahanPangan->energi,
                'protein'      => $d->bahanPangan->protein,
                'lemak'        => $d->bahanPangan->lemak,
                'karbohidrat'  => $d->bahanPangan->karbohidrat,
                'bdd'          => $d->bahanPangan->bdd,
                'jumlah_gram'  => $d->jumlah_gram,
                'jumlah_porsi' => $d->jumlah_porsi,
            ])->values();

        return view('menu-harian.edit', compact('menuHarian', 'existingBahans'));
    }

    public function update(Request $request, MenuHarian $menuHarian)
    {
        $this->authorizeUnit($menuHarian);

        $data = $request->validate([
            'nama_menu'        => 'nullable|string|max:200',
            'catatan'          => 'nullable|string|max:200',
            'status'           => 'required|in:draft,final',
            'kelompok'         => 'nullable|in:balita_sd3,sd4_ibu_menyusui',
            'bahans'           => 'nullable|array',
            'bahans.*.bahan_pangan_id' => 'required_with:bahans|exists:bahan_pangans,id',
            'bahans.*.jumlah_gram'     => 'required_with:bahans|numeric|min:0.01',
            'bahans.*.jumlah_porsi'    => 'nullable|integer|min:1',
        ]);

        $bahans   = $data['bahans'] ?? [];
        $kelompok = $data['kelompok'] ?? $menuHarian->kelompok ?? 'sd4_ibu_menyusui';

        $menuHarian->update([
            'nama_menu' => $data['nama_menu'] ?? $menuHarian->nama_menu,
            'catatan'   => $data['catatan'] ?? null,
            'status'    => $data['status'],
            'kelompok'  => $kelompok,
            'anggaran_per_porsi' => \App\Models\AnggaranPorsi::aktif(
                $menuHarian->tanggal->toDateString(), $kelompok
            ),
        ]);

        $menuHarian->detailBahans()->delete();

        foreach ($bahans as $b) {
            $menuHarian->detailBahans()->create([
                'bahan_pangan_id' => $b['bahan_pangan_id'],
                'jumlah_gram'     => $b['jumlah_gram'],
                'jumlah_porsi'    => $b['jumlah_porsi'] ?? 1,  // ← tambahkan ini
            ]);
        }

        return redirect()->route('menu-harian.show', $menuHarian)
            ->with('success', 'Menu berhasil diperbarui.');
    }

    public function destroy(MenuHarian $menuHarian)
    {
        $this->authorizeUnit($menuHarian);
        $menuHarian->delete();

        return redirect()->route('menu-harian.index')
            ->with('success', 'Menu berhasil dihapus.');
    }

    private function authorizeUnit(MenuHarian $menu): void
    {
        // Single SPPG — semua pengguna terautentikasi boleh akses
    }

    public function finalize(MenuHarian $menuHarian)
    {
        // Hanya pengelola yang boleh finalisasi
        if (auth()->user()->role !== 'pengelola') {
            abort(403);
        }

        // Hanya bisa finalisasi kalau masih draft
        if ($menuHarian->status !== 'draft') {
            return redirect()->route('menu-harian.show', $menuHarian)
                ->with('error', 'Menu sudah berstatus final.');
        }

        $menuHarian->update(['status' => 'final']);

        return redirect()->route('menu-harian.show', $menuHarian)
            ->with('success', 'Menu berhasil difinalisasi.');
    }
}