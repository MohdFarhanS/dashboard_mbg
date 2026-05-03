<?php
namespace App\Http\Controllers;

use App\Constants\AKG;
use App\Models\MenuHarian;
use Illuminate\Http\Request;
use Carbon\Carbon;

class GiziController extends Controller
{
    public function dashboard(Request $request)
    {
        $bulan = $request->input('bulan', now()->format('Y-m'));
        [$tahun, $bln] = explode('-', $bulan);

        $query = MenuHarian::with('detailBahans.bahanPangan')
            ->whereYear('tanggal', $tahun)
            ->whereMonth('tanggal', $bln)
            ->where('status', 'final')
            ->orderBy('tanggal');

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

        $menuHariIni = MenuHarian::with('detailBahans.bahanPangan')
            ->whereDate('tanggal', today())
            ->first();
        $giziHariIni = $menuHariIni ? $menuHariIni->totalGizi() : null;

        return view('gizi.dashboard', compact(
            'bulan', 'menus', 'trendData', 'rataGizi',
            'persenAkg', 'giziHariIni', 'menuHariIni',
            'jumlahHari'
        ));
    }

    // API endpoint untuk chart AJAX
    public function apiTrend(Request $request)
    {
        $bulan = $request->input('bulan', now()->format('Y-m'));
        [$tahun, $bln] = explode('-', $bulan);

        $query = MenuHarian::with('detailBahans.bahanPangan')
            ->whereYear('tanggal', $tahun)
            ->whereMonth('tanggal', $bln)
            ->where('status', 'final')
            ->orderBy('tanggal');

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