<?php

namespace App\Http\Controllers;

use App\Models\PesanMasuk;

class PesanMasukController extends Controller
{
    public function index()
    {
        $pesanList = PesanMasuk::latest()->paginate(20);

        // Tandai semua sebagai sudah dibaca setelah halaman dibuka
        PesanMasuk::unread()->update(['is_read' => true]);

        return view('pesan-masuk.index', compact('pesanList'));
    }
}
