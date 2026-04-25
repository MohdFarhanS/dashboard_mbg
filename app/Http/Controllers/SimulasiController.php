<?php
namespace App\Http\Controllers;

use App\Models\BahanPangan;
use App\Models\MenuHarian;
use App\Models\DetailBahan;
use App\Models\AnggaranPorsi;
use App\Models\HargaBahan;
use App\Constants\AKG;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class SimulasiController extends Controller
{
    public function index()
    {
        return view('simulasi.index');
    }

    public function kalkulasi(Request $request)
    {
        $request->validate([
            'bahans'         => 'required|array|min:1',
            'bahans.*.id'    => 'required|exists:bahan_pangans,id',
            'bahans.*.gram'  => 'required|numeric|min:1',
            'bahans.*.porsi' => 'required|integer|min:1',
            'jumlah_porsi'   => 'required|integer|min:1',
            'tanggal'        => 'nullable|date',
        ]);

        $jumlahPorsi = (int) $request->jumlah_porsi;
        $tanggal     = $request->tanggal ?? today()->toDateString();
        $unit        = Auth::user()->unit_sppg;

        $giziTotal  = ['energi'=>0,'protein'=>0,'lemak'=>0,'karbohidrat'=>0,
                        'serat'=>0,'kalsium'=>0,'besi'=>0,'vit_c'=>0];
        $biayaTotal = 0;
        $detail     = [];

        foreach ($request->bahans as $item) {
            $b      = BahanPangan::find($item['id']);
            $gram   = (float) $item['gram'];
            $porsi  = (int)   $item['porsi'];
            $bdd    = ($b->bdd ?? 100) / 100;
            $faktor = ($gram * $bdd) / 100;

            $giziItem = [];
            foreach (array_keys($giziTotal) as $k) {
                $val            = $faktor * ($b->$k ?? 0) * $porsi;
                $giziTotal[$k] += $val;
                $giziItem[$k]   = $val;
            }

            $harga = HargaBahan::where('bahan_pangan_id', $b->id)
                ->where('berlaku_mulai', '<=', $tanggal)
                ->where(function ($q) use ($tanggal) {
                    $q->whereNull('berlaku_sampai')
                      ->orWhere('berlaku_sampai', '>=', $tanggal);
                })
                ->orderByDesc('berlaku_mulai')
                ->value('harga_per_100g');

            $biayaItem  = $harga ? ($gram * $porsi / 100) * $harga : 0;
            $biayaTotal += $biayaItem;

            $detail[] = [
                'id'       => $b->id,
                'nama'     => $b->nama_bahan,
                'kode'     => $b->kode,
                'kategori' => $b->kategori,
                'gram'     => $gram,
                'porsi'    => $porsi,
                'bdd'      => $b->bdd,
                'gizi'     => $giziItem,
                'harga_per_100g' => $harga ?? 0,
                'biaya'    => round($biayaItem, 0),
                'ada_harga'=> $harga !== null,
            ];
        }

        $costPerPorsi = $jumlahPorsi > 0 ? $biayaTotal / $jumlahPorsi : 0;
        $anggaran     = AnggaranPorsi::aktif($unit, $tanggal);
        $totalAngg    = $anggaran * $jumlahPorsi;
        $selisih      = $totalAngg - $biayaTotal;
        $persenAngg   = $totalAngg > 0
            ? round($biayaTotal / $totalAngg * 100, 1) : 0;

        $persenAkg = [];
        foreach (array_keys($giziTotal) as $k) {
            $target        = AKG::MAKAN_SIANG[$k] ?? 1;
            $persenAkg[$k] = $target > 0
                ? round($giziTotal[$k] / $target * 100, 1) : 0;
        }

        return response()->json([
            'gizi'       => array_map(fn($v) => round($v, 2), $giziTotal),
            'persen_akg' => $persenAkg,
            'detail'     => $detail,
            'biaya'      => [
                'total'           => round($biayaTotal, 0),
                'cost_per_porsi'  => round($costPerPorsi, 0),
                'anggaran'        => $anggaran,
                'selisih'         => round($selisih, 0),
                'persen_anggaran' => $persenAngg,
            ],
        ]);
    }

    public function simpan(Request $request)
    {
        $request->validate([
            'tanggal'        => 'required|date',
            'nama_menu'      => 'nullable|string|max:100',
            'catatan'        => 'nullable|string|max:255',
            'jumlah_porsi'   => 'required|integer|min:1',
            'bahans'         => 'required|array|min:1',
            'bahans.*.id'    => 'required|exists:bahan_pangans,id',
            'bahans.*.gram'  => 'required|numeric|min:1',
            'bahans.*.porsi' => 'required|integer|min:1',
        ]);

        $existing = MenuHarian::where('unit_sppg', Auth::user()->unit_sppg)
            ->whereDate('tanggal', $request->tanggal)
            ->first();

        if ($existing) {
            return response()->json([
                'error'    => 'Menu untuk tanggal ini sudah ada. Silakan edit menu yang sudah ada.',
                'redirect' => route('menu-harian.edit', $existing),
            ], 422);
        }

        DB::transaction(function () use ($request) {
            $menu = MenuHarian::create([
                'unit_sppg'          => Auth::user()->unit_sppg,
                'tanggal'            => $request->tanggal,
                'nama_menu'          => $request->nama_menu,
                'catatan'            => $request->catatan,
                'jumlah_porsi'       => $request->jumlah_porsi,
                'anggaran_per_porsi' => AnggaranPorsi::aktif(
                    Auth::user()->unit_sppg, $request->tanggal
                ),
                'status'     => 'draft',
                'created_by' => Auth::id(),
            ]);

            foreach ($request->bahans as $item) {
                DetailBahan::create([
                    'menu_harian_id'  => $menu->id,
                    'bahan_pangan_id' => $item['id'],
                    'jumlah_gram'     => $item['gram'],
                    'jumlah_porsi'    => $item['porsi'],
                ]);
            }
        });

        return response()->json([
            'success'  => 'Simulasi berhasil disimpan sebagai Menu Harian (Draft).',
            'redirect' => route('menu-harian.index'),
        ]);
    }
}