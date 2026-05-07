<?php

namespace App\Http\Controllers;

use App\Models\MenuHarian;
use App\Models\AnggaranPorsi;
use App\Constants\AKG;
use App\Exports\LaporanGiziExport;
use App\Exports\LaporanBiayaExport;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Maatwebsite\Excel\Facades\Excel;
use Barryvdh\DomPDF\Facade\Pdf;
use Rap2hpoutre\FastExcel\FastExcel;

class LaporanController extends Controller
{
    public function index(Request $request)
    {
        $user  = Auth::user();
        $bulan = $request->input('bulan', now()->format('Y-m'));
        $jenis = $request->input('jenis', $user->isAkuntan() ? 'biaya' : 'gizi');

        if ($user->isAkuntan())    $jenis = 'biaya';
        if ($user->isAhliGizi())   $jenis = 'gizi';

        [$tahun, $bln] = explode('-', $bulan);

        $query = MenuHarian::with('detailBahans.bahanPangan')
            ->whereYear('tanggal', $tahun)
            ->whereMonth('tanggal', $bln)
            ->where('status', 'final')
            ->orderBy('tanggal');

        $menus = $query->get();

        $totalMenu  = $menus->count();
        $rataGizi   = $this->hitungRataGizi($menus);

        $rekapBiaya = $menus->map(fn($m) => $m->totalBiaya());
        $totalBiaya = $rekapBiaya->sum(fn($b) => $b['total_seluruh']);
        $rataCost   = $totalMenu > 0 ? $rekapBiaya->avg(fn($b) => $b['cost_per_porsi']) : 0;

        return view('laporan.index', compact(
            'menus', 'bulan', 'jenis',
            'totalMenu', 'rataGizi', 'totalBiaya', 'rataCost',
            'tahun', 'bln'
        ));
    }

    public function exportExcel(Request $request)
    {
        $user  = Auth::user();
        $bulan = $request->input('bulan', now()->format('Y-m'));
        $jenis = $request->input('jenis', $user->isAkuntan() ? 'biaya' : 'gizi');

        if ($user->isAkuntan())  $jenis = 'biaya';
        if ($user->isAhliGizi()) $jenis = 'gizi';
        $nama  = "Laporan_" . ucfirst($jenis) . "_" . $bulan . ".xlsx";

        [$tahun, $bln] = explode('-', $bulan);

        $query = MenuHarian::with('detailBahans.bahanPangan')
            ->whereYear('tanggal', $tahun)
            ->whereMonth('tanggal', $bln)
            ->where('status', 'final')
            ->orderBy('tanggal');

        $menus = $query->get();

        if ($jenis === 'biaya') {
            $rows = $menus->map(function ($menu, $i) {
                $b      = $menu->totalBiaya();
                $status = match($menu->statusAnggaran()) {
                    'over'    => 'Over Budget',
                    'warning' => 'Mendekati Batas',
                    'aman'    => 'Aman',
                    default   => '-',
                };
                return [
                    'No'                  => $i + 1,
                    'Tanggal'             => $menu->tanggal->format('d/m/Y'),
                    'Nama Menu'           => $menu->nama_menu ?? '-',
                    'Jumlah Porsi'        => $menu->jumlah_porsi ?? 1,
                    'Total Biaya (Rp)'    => round($b['total_seluruh']),
                    'Cost/Porsi (Rp)'     => round($b['cost_per_porsi']),
                    'Anggaran/Porsi (Rp)' => round($b['anggaran']),
                    'Selisih (Rp)'        => round($b['selisih']),
                    '% Anggaran'          => $b['persen_anggaran'] . '%',
                    'Status'              => $status,
                ];
            });
        } else {
            $akgRef = \App\Constants\AKG::MAKAN_SIANG;
            $rows = $menus->map(function ($menu, $i) use ($akgRef) {
                $g = $menu->totalGizi();
                return [
                    'No'              => $i + 1,
                    'Tanggal'         => $menu->tanggal->format('d/m/Y'),
                    'Nama Menu'       => $menu->nama_menu ?? '-',
                    'Energi (kkal)'   => round($g['energi'], 1),
                    '% AKG Energi'    => round($g['energi'] / $akgRef['energi'] * 100) . '%',
                    'Protein (g)'     => round($g['protein'], 1),
                    'Lemak (g)'       => round($g['lemak'], 1),
                    'Karbohidrat (g)' => round($g['karbohidrat'], 1),
                    'Serat (g)'       => round($g['serat'], 1),
                    'Kalsium (mg)'    => round($g['kalsium'], 1),
                    'Fe (mg)'         => round($g['besi'], 2),
                    'Vit C (mg)'      => round($g['vit_c'], 1),
                ];
            });
        }

        return (new FastExcel($rows))->download($nama);
    }

    public function exportPdf(Request $request)
    {
        $user  = Auth::user();
        $bulan = $request->input('bulan', now()->format('Y-m'));
        $jenis = $request->input('jenis', $user->isAkuntan() ? 'biaya' : 'gizi');

        if ($user->isAkuntan())  $jenis = 'biaya';
        if ($user->isAhliGizi()) $jenis = 'gizi';

        [$tahun, $bln] = explode('-', $bulan);

        $query = MenuHarian::with('detailBahans.bahanPangan')
            ->whereYear('tanggal', $tahun)
            ->whereMonth('tanggal', $bln)
            ->where('status', 'final')
            ->orderBy('tanggal');

        $menus     = $query->get();
        $rataGizi  = $this->hitungRataGizi($menus);
        $totalBiaya = $menus->sum(fn($m) => $m->totalBiaya()['total_seluruh']);
        $bulanLabel = \Carbon\Carbon::createFromFormat('Y-m', $bulan)->translatedFormat('F Y');

        $view = $jenis === 'biaya' ? 'laporan.pdf-biaya' : 'laporan.pdf-gizi';
        $nama = "Laporan_" . ucfirst($jenis) . "_" . $bulan . ".pdf";

        $pdf = Pdf::loadView($view, compact(
            'menus', 'bulan', 'bulanLabel',
            'rataGizi', 'totalBiaya', 'user'
        ))->setPaper('a4', 'landscape');

        return $pdf->download($nama);
    }

    private function hitungRataGizi($menus): array
    {
        $keys  = ['energi', 'protein', 'lemak', 'karbohidrat', 'serat', 'kalsium', 'besi', 'vit_c'];
        $total = array_fill_keys($keys, 0);
        $count = $menus->count();

        foreach ($menus as $menu) {
            $gizi = $menu->totalGizi();
            foreach ($keys as $k) {
                $total[$k] += $gizi[$k] ?? 0;
            }
        }

        if ($count === 0) return $total;

        return array_map(fn($v) => round($v / $count, 1), $total);
    }
}