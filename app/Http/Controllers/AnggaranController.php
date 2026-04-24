<?php

namespace App\Http\Controllers;

use App\Models\AnggaranPorsi;
use App\Models\MenuHarian;
use Illuminate\Http\Request;

class AnggaranController extends Controller
{
    public function __construct()
    {
        // Semua method di controller ini hanya untuk admin
        if (auth()->check() && auth()->user()->role !== 'admin') {
            abort(403);
        }
    }

    public function index()
    {
        $riwayat = AnggaranPorsi::with('createdBy')
            ->orderByDesc('berlaku_mulai')
            ->paginate(20);

        return view('anggaran.index', compact('riwayat'));
    }

    public function create()
    {
        $unitList = \App\Models\MenuHarian::distinct()->pluck('unit_sppg')->sort()->values();
        return view('anggaran.form', compact('unitList'));
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'unit_sppg'          => 'required|string',
            'anggaran_per_porsi' => 'required|numeric|min:1000',
            'berlaku_mulai'      => 'required|date',
            'berlaku_sampai'     => 'nullable|date|after_or_equal:berlaku_mulai',
            'keterangan'         => 'nullable|string|max:200',
        ]);

        $data['created_by'] = auth()->id();
        AnggaranPorsi::create($data);

        return redirect()->route('anggaran.index')
            ->with('success', 'Anggaran baru berhasil ditetapkan.');
    }

    public function edit(AnggaranPorsi $anggaran)
    {
        $unitList = \App\Models\MenuHarian::distinct()->pluck('unit_sppg')->sort()->values();
        return view('anggaran.form', compact('anggaran', 'unitList'));
    }

    public function update(Request $request, AnggaranPorsi $anggaran)
    {
        $data = $request->validate([
            'unit_sppg'          => 'required|string',
            'anggaran_per_porsi' => 'required|numeric|min:1000',
            'berlaku_mulai'      => 'required|date',
            'berlaku_sampai'     => 'nullable|date|after_or_equal:berlaku_mulai',
            'keterangan'         => 'nullable|string|max:200',
        ]);

        $anggaran->update($data);

        return redirect()->route('anggaran.index')
            ->with('success', 'Anggaran berhasil diperbarui.');
    }
}
