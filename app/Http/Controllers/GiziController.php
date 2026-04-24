<?php
namespace App\Http\Controllers;

use App\Constants\AKG;
use App\Models\MenuHarian;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;

class GiziController extends Controller
{
    public function dashboard(Request $request)
    {
        $user = Auth::user();
        $isAdmin = $user->role === 'admin';

        // Periode filter — default: bulan ini
        $bulan = $request->input('bulan', now()->format('Y-m'));
        [$tahun, $bln] = explode('-', $bulan);

        // FIX: Admin lihat semua unit, pengelola hanya unitnya
        $query = MenuHarian::with('detailBahans.bahanPangan')
            ->whereYear('tanggal', $tahun)
            ->whereMonth('tanggal', $bln)
            ->where('status', 'final')
            ->orderBy('tanggal');

        if (!$isAdmin) {
            $query->where('unit_sppg', $user->unit_sppg);
        }

        // FIX: Admin bisa filter per unit via dropdown
        $filterUnit = $request->input('unit_sppg', '');
        if ($isAdmin && $filterUnit) {
            $query->where('unit_sppg', $filterUnit);
        }

        $menus = $query->get();

        // Hitung total gizi per hari → untuk chart tren
        $trendData = [];
        foreach ($menus as $menu) {
            $gizi = $menu->totalGizi();
            $trendData[] = [
                'tanggal'     => $menu->tanggal->format('d/m'),
                'energi'      => $gizi['energi'],
                'protein'     => $gizi['protein'],
                'lemak'       => $gizi['lemak'],
                'karbohidrat' => $gizi['karbohidrat'],
            ];
        }

        // Rata-rata gizi bulan ini
        $keys      = ['energi','protein','lemak','karbohidrat','serat','kalsium','besi','vit_c'];
        $totalGizi = array_fill_keys($keys, 0);
        $jumlahHari = $menus->count();

        foreach ($menus as $menu) {
            $gizi = $menu->totalGizi();
            foreach ($keys as $k) {
                $totalGizi[$k] += $gizi[$k] ?? 0;
            }
        }

        $rataGizi = [];
        foreach ($keys as $k) {
            $rataGizi[$k] = $jumlahHari > 0 ? round($totalGizi[$k] / $jumlahHari, 1) : 0;
        }

        // Persentase vs AKG makan siang
        $persenAkg = [];
        foreach ($keys as $k) {
            $acuan = AKG::MAKAN_SIANG[$k] ?? 1;
            $persenAkg[$k] = $acuan > 0 ? min(round(($rataGizi[$k] / $acuan) * 100, 1), 200) : 0;
        }

        // FIX: Status hari ini — admin lihat semua, pengelola unitnya saja
        $queryHariIni = MenuHarian::with('detailBahans.bahanPangan')
            ->whereDate('tanggal', today());

        if (!$isAdmin) {
            $queryHariIni->where('unit_sppg', $user->unit_sppg);
        } elseif ($filterUnit) {
            $queryHariIni->where('unit_sppg', $filterUnit);
        }

        $menuHariIni = $queryHariIni->first();
        $giziHariIni = $menuHariIni ? $menuHariIni->totalGizi() : null;

        // FIX: List unit untuk dropdown filter (admin only)
        $unitList = $isAdmin
            ? MenuHarian::distinct()->pluck('unit_sppg')->sort()->values()
            : collect();

        return view('gizi.dashboard', compact(
            'bulan', 'menus', 'trendData', 'rataGizi',
            'persenAkg', 'giziHariIni', 'menuHariIni',
            'jumlahHari', 'unitList', 'filterUnit'
        ));
    }

    // API endpoint untuk chart AJAX
    public function apiTrend(Request $request)
    {
        $user   = Auth::user();
        $isAdmin = $user->role === 'admin';
        $bulan  = $request->input('bulan', now()->format('Y-m'));
        [$tahun, $bln] = explode('-', $bulan);

        // FIX: Admin lihat semua unit
        $query = MenuHarian::with('detailBahans.bahanPangan')
            ->whereYear('tanggal', $tahun)
            ->whereMonth('tanggal', $bln)
            ->where('status', 'final')
            ->orderBy('tanggal');

        if (!$isAdmin) {
            $query->where('unit_sppg', $user->unit_sppg);
        }

        if ($isAdmin && $request->input('unit_sppg')) {
            $query->where('unit_sppg', $request->input('unit_sppg'));
        }

        $menus = $query->get();

        $data = $menus->map(function ($m) {
            $g = $m->totalGizi();
            return ['tanggal' => $m->tanggal->format('d/m')] + $g;
        });

        return response()->json([
            'data'  => $data,
            'akg'   => AKG::MAKAN_SIANG,
            'label' => AKG::LABEL,
        ]);
    }
}