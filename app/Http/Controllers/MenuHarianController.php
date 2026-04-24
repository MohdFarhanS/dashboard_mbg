<?php

namespace App\Http\Controllers;

use App\Models\MenuHarian;
use App\Models\BahanPangan;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class MenuHarianController extends Controller
{
    public function index(Request $request)
    {
        $user = Auth::user();

        // FIX: Admin lihat semua menu, pengelola hanya unitnya
        $query = MenuHarian::with('detailBahans')
            ->orderByDesc('tanggal');

        if ($user->role !== 'admin') {
            $query->where('unit_sppg', $user->unit_sppg);
        }

        // Filter tambahan untuk admin: filter per unit
        if ($user->role === 'admin' && $request->input('unit_sppg')) {
            $query->where('unit_sppg', $request->input('unit_sppg'));
        }

        $menus = $query->paginate(15)->withQueryString();

        // Untuk filter dropdown unit (admin only)
        $unitList = $user->role === 'admin'
            ? MenuHarian::distinct()->pluck('unit_sppg')->sort()->values()
            : collect();

        return view('menu-harian.index', compact('menus', 'unitList'));
    }

    public function create()
    {
        if (auth()->user()->role !== 'pengelola') {
            abort(403);
        }

        $user   = Auth::user();
        $bahans = BahanPangan::select('id', 'nama_bahan', 'bdd')->orderBy('nama_bahan')->get();

        $existing = MenuHarian::where('unit_sppg', $user->unit_sppg)
            ->whereDate('tanggal', today())
            ->first();

        return view('menu-harian.create', compact('bahans', 'existing'));
    }

    public function store(Request $request)
    {
        if (auth()->user()->role !== 'pengelola') {
            abort(403);
        }
        
        $user = Auth::user();

        $data = $request->validate([
            'tanggal'          => 'required|date',
            'nama_menu'        => 'nullable|string|max:200',  // nullable karena null
            'catatan'          => 'nullable|string|max:200',
            'status'           => 'nullable|in:draft,final',
            'bahans'           => 'nullable|array',
            'bahans.*.bahan_pangan_id' => 'required_with:bahans|exists:bahan_pangans,id',
            'bahans.*.jumlah_gram'     => 'required_with:bahans|numeric|min:0.01',
            'bahans.*.jumlah_porsi'    => 'nullable|integer|min:1',
        ]);

        // Ambil jumlah_porsi dari bahan pertama (karena ada di dalam array bahans)
        $bahans       = $data['bahans'] ?? [];
        $jumlahPorsi  = !empty($bahans) ? ($bahans[0]['jumlah_porsi'] ?? 1) : 1;

        // Cek duplikat
        $existing = MenuHarian::where('unit_sppg', $user->unit_sppg)
            ->whereDate('tanggal', $data['tanggal'])
            ->first();

        if ($existing) {
            return redirect()->route('menu-harian.edit', $existing)
                ->with('warning', 'Menu untuk tanggal ini sudah ada. Silakan edit menu yang sudah ada.');
        }

        $menu = MenuHarian::create([
            'tanggal'            => $data['tanggal'],
            'nama_menu'          => $data['nama_menu'] ?? '-',
            'catatan_anggaran'   => $data['catatan'] ?? null,
            'status'             => $data['status'] ?? 'draft',
            'unit_sppg'          => $user->unit_sppg,
            'user_id'            => $user->id,
            'anggaran_per_porsi' => 15000,
            'jumlah_porsi'       => $jumlahPorsi,
        ]);

        // Simpan detail bahan
        foreach ($bahans as $b) {
            $menu->detailBahans()->create([
                'bahan_pangan_id' => $b['bahan_pangan_id'],
                'jumlah_gram'     => $b['jumlah_gram'],
            ]);
        }

        return redirect()->route('menu-harian.index')
            ->with('success', 'Menu berhasil disimpan.');
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
        $existingBahans = $menuHarian->detailBahans->map(fn($d) => [
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
        ]);

        return view('menu-harian.edit', compact('menuHarian', 'existingBahans'));
    }

    public function update(Request $request, MenuHarian $menuHarian)
    {
        $this->authorizeUnit($menuHarian);

        $data = $request->validate([
            'nama_menu'        => 'nullable|string|max:200',
            'catatan'          => 'nullable|string|max:200',
            'status'           => 'required|in:draft,final',
            'bahans'           => 'nullable|array',
            'bahans.*.bahan_pangan_id' => 'required_with:bahans|exists:bahan_pangans,id',
            'bahans.*.jumlah_gram'     => 'required_with:bahans|numeric|min:0.01',
            'bahans.*.jumlah_porsi'    => 'nullable|integer|min:1',
        ]);

        $bahans = $data['bahans'] ?? [];

        $menuHarian->update([
            'nama_menu' => $data['nama_menu'] ?? $menuHarian->nama_menu,
            'catatan'   => $data['catatan'] ?? null,
            'status'    => $data['status'],
        ]);

        $menuHarian->detailBahans()->delete();

        foreach ($bahans as $b) {
            $menuHarian->detailBahans()->create([
                'bahan_pangan_id' => $b['bahan_pangan_id'],
                'jumlah_gram'     => $b['jumlah_gram'],
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

    // FIX: Authorization helper
    private function authorizeUnit(MenuHarian $menu): void
    {
        $user = Auth::user();
        if ($user->role !== 'admin' && $user->unit_sppg !== $menu->unit_sppg) {
            abort(403, 'Anda tidak memiliki akses ke menu unit ini.');
        }
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