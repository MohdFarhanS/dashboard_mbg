<?php

namespace App\Http\Controllers;

use App\Models\MenuHarian;
use App\Constants\AKG;
use Illuminate\Support\Facades\Auth;

class DashboardController extends Controller
{
    public function index()
    {
        $user   = Auth::user();
        $today  = today();

        // ── Menu hari ini ──
        $menusHariIni = MenuHarian::with('detailBahans.bahanPangan')
            ->whereDate('tanggal', $today)
            ->get();

        // ── Akumulasi gizi hari ini ──
        $totalKalori    = 0;
        $totalProtein   = 0;
        $totalKarbo     = 0;
        $totalLemak     = 0;
        $totalBiaya     = 0;

        foreach ($menusHariIni as $menu) {
            $gizi = $menu->totalGizi();
            $totalKalori  += $gizi['energi']      ?? 0;
            $totalProtein += $gizi['protein']     ?? 0;
            $totalKarbo   += $gizi['karbohidrat'] ?? 0;
            $totalLemak   += $gizi['lemak']       ?? 0;

            $biaya = $menu->totalBiaya();
            $totalBiaya += $biaya['total_seluruh'] ?? 0;
        }

        // ── Target AKG harian (3 sesi makan = kalikan 3) ──
        $targetKalori   = AKG::MAKAN_SIANG['energi']      * 3;
        $targetProtein  = AKG::MAKAN_SIANG['protein']     * 3;
        $targetKarbo    = AKG::MAKAN_SIANG['karbohidrat'] * 3;
        $targetLemak    = AKG::MAKAN_SIANG['lemak']       * 3;

        $persenProtein  = $targetProtein > 0
            ? min(round($totalProtein / $targetProtein * 100), 200) : 0;
        $persenKarbo    = $targetKarbo > 0
            ? min(round($totalKarbo   / $targetKarbo   * 100), 200) : 0;
        $persenLemak    = $targetLemak > 0
            ? min(round($totalLemak   / $targetLemak   * 100), 200) : 0;

        // ── Budget harian: anggaran per porsi × total porsi ──
        $budgetHarian = 0;
        foreach ($menusHariIni as $menu) {
            $b = $menu->totalBiaya();
            $budgetHarian += $b['anggaran'] * ($menu->jumlah_porsi ?? 1);
        }
        // fallback jika anggaran belum di-set
        if ($budgetHarian === 0 && $menusHariIni->count()) {
            $budgetHarian = 15000 * $menusHariIni->sum('jumlah_porsi');
        }

        // ── Status budget ──
        $selisih       = $budgetHarian - $totalBiaya;
        $persenBiaya   = $budgetHarian > 0
            ? round($totalBiaya / $budgetHarian * 100) : 0;
        $statusBudget  = $persenBiaya > 100 ? 'over'
            : ($persenBiaya >= 85 ? 'warning' : 'aman');

        // ── Budget Alert: hitung menu final bulan ini yang over/warning ──
        $menusFinal = MenuHarian::where('status', 'final')
            ->whereYear('tanggal',  $today->year)
            ->whereMonth('tanggal', $today->month)
            ->get();
        $alertOver       = 0;
        $alertWarning    = 0;
        $alertList       = [];
        
        foreach ($menusFinal as $menu) {
            $status = $menu->statusAnggaran();
            if ($status === 'over') {
                $alertOver++;
                $alertList[] = [
                    'type'    => 'danger',
                    'msg'     => 'Menu ' . ($menu->nama_menu ?? $menu->tanggal->format('d/m/Y'))
                                 . ' melebihi anggaran',
                    'time'    => $menu->tanggal->format('d/m/Y'),
                    'menu_id' => $menu->id,
                ];
            } elseif ($status === 'warning') {
                $alertWarning++;
                $alertList[] = [
                    'type'    => 'warning',
                    'msg'     => 'Menu ' . ($menu->nama_menu ?? $menu->tanggal->format('d/m/Y'))
                                 . ' mendekati batas anggaran',
                    'time'    => $menu->tanggal->format('d/m/Y'),
                    'menu_id' => $menu->id,
                ];
            }
        }
        $totalAlert = $alertOver + $alertWarning;

        $distribusiBiaya = [];

        foreach ($menusHariIni as $menu) {
            foreach ($menu->detailBahans as $detail) {
                $bahan = $detail->bahanPangan;
                if (!$bahan) continue;
                $kategori = $bahan->kategori ?? 'Lainnya';

                $harga = \App\Models\HargaBahan::where('bahan_pangan_id', $bahan->id)
                    ->where('berlaku_mulai', '<=', today())
                    ->where(function ($q) {
                        $q->whereNull('berlaku_sampai')
                          ->orWhere('berlaku_sampai', '>=', today());
                    })
                    ->orderByDesc('berlaku_mulai')
                    ->value('harga_per_100g');
    
                if (!$harga) continue;
    
                $biayaBahan = ($detail->jumlah_gram / 100) * $harga * ($detail->jumlah_porsi ?? 1);
                $distribusiBiaya[$kategori] = ($distribusiBiaya[$kategori] ?? 0) + $biayaBahan;
            }
        }
    
        arsort($distribusiBiaya);
        $top     = array_slice($distribusiBiaya, 0, 6, true);
        $sisanya = array_sum($distribusiBiaya) - array_sum($top);
        if ($sisanya > 0) {
            $top['Lainnya'] = ($top['Lainnya'] ?? 0) + $sisanya;
        }

        $stats = [
            'distribusi_biaya'    => $top, 
            'total_menu_hari_ini' => $menusHariIni->count(),
            'total_kalori'        => round($totalKalori),
            'target_kalori'       => $targetKalori,
            'total_biaya'         => round($totalBiaya),
            'budget_harian'       => $budgetHarian,
            'status_budget'       => $statusBudget,
            'persen_protein'      => $persenProtein,
            'persen_karbohidrat'  => $persenKarbo,
            'persen_lemak'        => $persenLemak,
            'alert_over'          => $alertOver,
            'alert_warning'       => $alertWarning,
            'total_alert'         => $totalAlert,
            'alert_list'          => array_slice($alertList, 0, 5),
        ];

        return view('dashboard.index', compact('user', 'stats', 'menusHariIni'));
    }
}