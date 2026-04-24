<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class DashboardController extends Controller
{
    public function index()
    {
        $user = Auth::user();

        // Data dummy untuk sekarang, nanti diganti dari DB
        $stats = [
            'total_menu_hari_ini'   => 3,
            'total_kalori'          => 2150,
            'target_kalori'         => 2100,
            'total_biaya'           => 285000,
            'budget_harian'         => 300000,
            'status_budget'         => 'aman',   // 'aman' | 'warning' | 'over'
            'persen_protein'        => 72,
            'persen_karbohidrat'    => 85,
            'persen_lemak'          => 60,
        ];

        return view('dashboard.index', compact('user', 'stats'));
    }
}