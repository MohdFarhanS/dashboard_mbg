<?php

namespace App\Http\Controllers;

use App\Models\PesanMasuk;
use Illuminate\Http\Request;

class LandingController extends Controller
{
    public function index()
    {
        return view('landing');
    }

    public function kirimPesan(Request $request)
    {
        $request->validate([
            'nama'  => 'required|string|max:100',
            'no_hp' => ['required', 'string', 'max:20', 'regex:/^[0-9+\-\s()]{8,20}$/'],
            'pesan' => 'required|string|max:1000',
        ], [
            'nama.required'  => 'Nama wajib diisi.',
            'no_hp.required' => 'Nomor HP wajib diisi.',
            'no_hp.regex'    => 'Format nomor HP tidak valid (contoh: 08123456789).',
            'pesan.required' => 'Pesan wajib diisi.',
            'pesan.max'      => 'Pesan maksimal 1000 karakter.',
        ]);

        PesanMasuk::create($request->only('nama', 'no_hp', 'pesan'));

        return redirect()->route('landing')->with('pesan_terkirim', true);
    }
}
