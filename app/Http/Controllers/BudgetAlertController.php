<?php

namespace App\Http\Controllers;

use App\Models\MenuHarian;
use App\Constants\AKG;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class BudgetAlertController extends Controller
{
    public function index(Request $request)
    {
        $user      = Auth::user();
        $bulan     = $request->input('bulan', today()->format('Y-m'));
        $severity  = $request->input('severity', '');

        [$tahun, $bulanAngka] = explode('-', $bulan);

        $query = MenuHarian::with('detailBahans.bahanPangan')
            ->where('status', 'final')
            ->whereYear('tanggal',  $tahun)
            ->whereMonth('tanggal', $bulanAngka)
            ->orderBy('tanggal', 'desc');

        if ($user->role === 'pengelola') {
            $query->where('unit_sppg', $user->unit_sppg);
        } elseif ($request->filled('unit_sppg')) {
            $query->where('unit_sppg', $request->unit_sppg);
        }

        $menusFinal = $query->get();

        // Klasifikasi tiap menu
        $alerts        = [];
        $countOver     = 0;
        $countWarning  = 0;
        $countAman     = 0;
        $alertMenuIds  = [];

        foreach ($menusFinal as $menu) {
            $status = $menu->statusAnggaran();
            $biaya  = $menu->totalBiaya();

            if ($status === 'over') {
                $countOver++;
                $alertMenuIds[] = $menu->id;
            } elseif ($status === 'warning') {
                $countWarning++;
                $alertMenuIds[] = $menu->id;
            } else {
                $countAman++;
            }

            // Filter severity
            if ($severity === 'over'    && $status !== 'over')    continue;
            if ($severity === 'warning' && $status !== 'warning') continue;
            if ($severity === 'aman'    && $status !== 'aman')    continue;

            // Hanya tampilkan over dan warning jika tidak ada filter
            if ($severity === '' && $status === 'aman') continue;

            $alerts[] = [
                'menu'         => $menu,
                'status'       => $status,
                'biaya'        => $biaya,
                'cost_porsi'   => $biaya['cost_per_porsi'],
                'anggaran'     => $biaya['anggaran'],
                'selisih'      => $biaya['selisih'],
                'persen'       => $biaya['persen_anggaran'],
            ];
        }

        // Tandai alert bulan ini sebagai sudah dilihat (dismiss notifikasi sidebar/navbar)
        if ($bulan === today()->format('Y-m')) {
            $existing = session('dismissed_alert_ids', []);
            session(['dismissed_alert_ids' => array_unique(array_merge($existing, $alertMenuIds))]);
        }

        // Unit list untuk filter admin
        $unitList = $user->role === 'admin'
            ? MenuHarian::distinct()->pluck('unit_sppg')->sort()->values()
            : collect();

        return view('budget-alert.index', compact(
            'alerts', 'bulan', 'severity', 'unitList',
            'countOver', 'countWarning', 'countAman'
        ));
    }
}