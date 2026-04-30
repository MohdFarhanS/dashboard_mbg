<?php

namespace App\Http\Controllers;

use App\Models\AnggaranPorsi;
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
        if (auth()->user()->role !== 'admin') abort(403);
        $riwayat = AnggaranPorsi::with('createdBy')
            ->orderByDesc('berlaku_mulai')
            ->paginate(20);

        return view('anggaran.index', compact('riwayat'));
    }

    public function create()
    {
        if (auth()->user()->role !== 'admin') abort(403);
        return view('anggaran.form');
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'anggaran_per_porsi' => 'required|numeric|min:1000',
            'berlaku_mulai'      => 'required|date',
            'keterangan'         => 'nullable|string|max:200',
        ]);

        $data['created_by'] = auth()->id();

        // Tutup anggaran aktif sebelumnya (set berlaku_sampai = berlaku_mulai baru - 1 hari)
        AnggaranPorsi::whereNull('berlaku_sampai')
            ->update(['berlaku_sampai' => \Carbon\Carbon::parse($data['berlaku_mulai'])->subDay()->toDateString()]);

        AnggaranPorsi::create($data);

        return redirect()->route('anggaran.index')
            ->with('success', 'Anggaran baru berhasil ditetapkan.');
    }
}
