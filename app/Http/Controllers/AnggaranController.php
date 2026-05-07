<?php

namespace App\Http\Controllers;

use App\Models\AnggaranPorsi;
use Carbon\Carbon;
use Illuminate\Http\Request;

class AnggaranController extends Controller
{
    // Route dilindungi middleware role:ketua_sppg — tidak perlu cek manual
    public function __construct() {}

    public function index()
    {
        $riwayat = AnggaranPorsi::with('createdBy')
            ->orderByDesc('berlaku_mulai')
            ->orderBy('kelompok')
            ->paginate(20);

        return view('anggaran.index', compact('riwayat'));
    }

    public function create()
    {
        return view('anggaran.form');
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'anggaran_balita_sd3'       => 'required|numeric|min:1000',
            'anggaran_sd4_ibu_menyusui' => 'required|numeric|min:1000',
            'berlaku_mulai'             => 'required|date',
            'keterangan'                => 'nullable|string|max:200',
        ]);

        $berlakuMulai = Carbon::parse($data['berlaku_mulai']);
        $berlakuSampaiLama = $berlakuMulai->clone()->subDay()->toDateString();

        $kelompokList = [
            'balita_sd3'       => (float) $data['anggaran_balita_sd3'],
            'sd4_ibu_menyusui' => (float) $data['anggaran_sd4_ibu_menyusui'],
        ];

        foreach ($kelompokList as $kelompok => $anggaran) {
            // Tutup record aktif sebelumnya untuk kelompok ini
            AnggaranPorsi::where('kelompok', $kelompok)
                ->whereNull('berlaku_sampai')
                ->update(['berlaku_sampai' => $berlakuSampaiLama]);

            AnggaranPorsi::create([
                'kelompok'         => $kelompok,
                'anggaran_per_porsi' => $anggaran,
                'berlaku_mulai'    => $data['berlaku_mulai'],
                'berlaku_sampai'   => null,
                'keterangan'       => $data['keterangan'] ?? null,
                'created_by'       => auth()->id(),
            ]);
        }

        return redirect()->route('anggaran.index')
            ->with('success', 'Anggaran baru berhasil ditetapkan untuk kedua kelompok.');
    }
}
