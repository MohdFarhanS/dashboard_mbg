<?php
namespace App\Http\Controllers;

use App\Models\BahanPangan;
use App\Models\MenuHarian;
use App\Models\MenuDetailBahan;
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
        $today = today()->toDateString();
        $anggaranBalitaSd3       = AnggaranPorsi::aktif($today, 'balita_sd3');
        $anggaranSd4IbuMenyusui  = AnggaranPorsi::aktif($today, 'sd4_ibu_menyusui');
        return view('simulasi.index', compact('anggaranBalitaSd3', 'anggaranSd4IbuMenyusui'));
    }

    public function kalkulasi(Request $request)
    {
        // Route dilindungi middleware role:ahli_gizi
        $request->validate([
            'bahans'         => 'required|array|min:1',
            'bahans.*.id'    => 'required|exists:bahan_pangans,id',
            'bahans.*.gram'  => 'required|numeric|min:1',
            'bahans.*.porsi' => 'required|integer|min:1',
            'jumlah_porsi'   => 'required|integer|min:1',
            'tanggal'        => 'nullable|date',
            'kelompok'       => 'nullable|in:balita_sd3,sd4_ibu_menyusui',
        ]);

        $jumlahPorsi = (int) $request->jumlah_porsi;
        $tanggal     = $request->tanggal ?? today()->toDateString();

        $giziTotal  = ['energi'=>0,'protein'=>0,'lemak'=>0,'karbohidrat'=>0,
                        'serat'=>0,'kalsium'=>0,'besi'=>0,'vit_c'=>0];
        $biayaTotal = 0;
        $detail     = [];

        foreach ($request->bahans as $item) {
            $b = BahanPangan::find($item['id']);
            if (!$b) continue;

            $gram  = (float) $item['gram'];
            $porsi = (int)   $item['porsi'];
            $bdd   = ($b->bdd ?? 100) / 100;
            $faktor = ($gram * $bdd) / 100;

            $giziItem = [];
            foreach (array_keys($giziTotal) as $k) {
                $val            = $faktor * ($b->$k ?? 0) * $porsi;
                $giziTotal[$k] += $val;
                $giziItem[$k]   = $val;
            }

            $hargaVal   = HargaBahan::hargaAktif($b->id, $tanggal);
            $biayaItem  = $hargaVal > 0 ? ($gram * $porsi / 100) * $hargaVal : 0;
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
                'harga_per_100g' => $hargaVal,
                'biaya'    => round($biayaItem, 0),
                'ada_harga'=> $hargaVal > 0,
            ];
        }

        $costPerPorsi = $jumlahPorsi > 0 ? $biayaTotal / $jumlahPorsi : 0;

        $anggaranPerKelompok = [
            'balita_sd3'       => AnggaranPorsi::aktif($tanggal, 'balita_sd3'),
            'sd4_ibu_menyusui' => AnggaranPorsi::aktif($tanggal, 'sd4_ibu_menyusui'),
        ];
        // Gunakan kelompok yang dipilih dari request, fallback ke sd4_ibu_menyusui
        $kelompokDipilih = in_array($request->kelompok, array_keys($anggaranPerKelompok))
            ? $request->kelompok
            : 'sd4_ibu_menyusui';
        $anggaran     = $anggaranPerKelompok[$kelompokDipilih];
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
                'total'                => round($biayaTotal, 0),
                'cost_per_porsi'       => round($costPerPorsi, 0),
                'anggaran'             => $anggaran,
                'selisih'              => round($selisih, 0),
                'persen_anggaran'      => $persenAngg,
                'anggaran_per_kelompok'=> $anggaranPerKelompok,
            ],
        ]);
    }

    public function editMenu(MenuHarian $menuHarian)
    {
        // Route dilindungi middleware role:ahli_gizi
        if ($menuHarian->status === 'final') {
            return redirect()->route('menu-harian.show', $menuHarian)
                ->with('error', 'Menu sudah final, tidak bisa diedit.');
        }

        $menuHarian->load('detailBahans.bahanPangan');

        $today = $menuHarian->tanggal->toDateString();

        $existingBahans = $menuHarian->detailBahans
            ->filter(fn($d) => $d->bahanPangan)
            ->map(fn($d) => [
                'id'             => $d->bahanPangan->id,
                'kode'           => $d->bahanPangan->kode,
                'nama_bahan'     => $d->bahanPangan->nama_bahan,
                'kategori'       => $d->bahanPangan->kategori,
                'energi'         => $d->bahanPangan->energi,
                'protein'        => $d->bahanPangan->protein,
                'lemak'          => $d->bahanPangan->lemak,
                'karbohidrat'    => $d->bahanPangan->karbohidrat,
                'bdd'            => $d->bahanPangan->bdd,
                'jumlah_gram'    => $d->jumlah_gram,
                'jumlah_porsi'   => $d->jumlah_porsi,
                'harga_per_100g' => HargaBahan::hargaAktif($d->bahanPangan->id, $today),
            ])->values();

        $anggaranBalitaSd3      = AnggaranPorsi::aktif($today, 'balita_sd3');
        $anggaranSd4IbuMenyusui = AnggaranPorsi::aktif($today, 'sd4_ibu_menyusui');

        return view('simulasi.index', compact(
            'menuHarian', 'existingBahans',
            'anggaranBalitaSd3', 'anggaranSd4IbuMenyusui'
        ));
    }

    public function simpan(Request $request)
    {
        // Route dilindungi middleware role:ahli_gizi
        $request->validate([
            'tanggal'        => 'required|date',
            'nama_menu'      => 'nullable|string|max:100',
            'catatan'        => 'nullable|string|max:255',
            'jumlah_porsi'   => 'required|integer|min:1',
            'bahans'         => 'required|array|min:1',
            'bahans.*.id'    => 'required|exists:bahan_pangans,id',
            'bahans.*.gram'  => 'required|numeric|min:1',
            'bahans.*.porsi' => 'required|integer|min:1',
            'menu_id'        => 'nullable|integer|exists:menu_harians,id',
            'kelompok'       => 'nullable|in:balita_sd3,sd4_ibu_menyusui',
        ]);

        $kelompok = $request->kelompok ?? 'sd4_ibu_menyusui';

        // ── Mode edit: perbarui menu yang sudah ada ─────────────────────────
        if ($request->filled('menu_id')) {
            $menu = MenuHarian::findOrFail($request->menu_id);

            DB::transaction(function () use ($request, $menu, $kelompok) {
                $menu->update([
                    'nama_menu'          => $request->nama_menu,
                    'catatan_anggaran'   => $request->catatan,
                    'jumlah_porsi'       => $request->jumlah_porsi,
                    'kelompok'           => $kelompok,
                    'anggaran_per_porsi' => AnggaranPorsi::aktif($menu->tanggal->toDateString(), $kelompok),
                ]);

                $menu->detailBahans()->delete();

                foreach ($request->bahans as $item) {
                    MenuDetailBahan::create([
                        'menu_harian_id'  => $menu->id,
                        'bahan_pangan_id' => $item['id'],
                        'jumlah_gram'     => $item['gram'],
                        'jumlah_porsi'    => $item['porsi'],
                    ]);
                }
            });

            return response()->json([
                'success'  => 'Menu berhasil diperbarui.',
                'redirect' => route('menu-harian.show', $menu),
            ]);
        }

        // ── Mode tambah baru ─────────────────────────────────────────────────
        $existing = MenuHarian::whereDate('tanggal', $request->tanggal)->first();

        if ($existing) {
            return response()->json([
                'error'    => 'Menu untuk tanggal ini sudah ada. Silakan edit menu yang sudah ada.',
                'redirect' => route('simulasi.edit-simulasi', $existing),
            ], 422);
        }

        DB::transaction(function () use ($request, $kelompok) {
            $menu = MenuHarian::create([
                'tanggal'            => $request->tanggal,
                'nama_menu'          => $request->nama_menu,
                'catatan_anggaran'   => $request->catatan,
                'jumlah_porsi'       => $request->jumlah_porsi,
                'kelompok'           => $kelompok,
                'anggaran_per_porsi' => AnggaranPorsi::aktif($request->tanggal, $kelompok),
                'status'             => 'draft',
                'user_id'            => Auth::id(),
            ]);

            foreach ($request->bahans as $item) {
                MenuDetailBahan::create([
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