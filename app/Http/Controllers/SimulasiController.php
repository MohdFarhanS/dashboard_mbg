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
    public function __construct()
    {
        if (auth()->check() && auth()->user()->role !== 'pengelola') {
            abort(403);
        }
    }

    public function index()
    {
        if (auth()->check() && auth()->user()->role !== 'pengelola') {
            abort(403);
        }
        return view('simulasi.index');
    }

    public function kalkulasi(Request $request)
    {
        if (Auth::user()->role === 'admin') {
            return response()->json(['message' => 'Admin tidak dapat menggunakan fitur simulasi.'], 403);
        }

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
        $anggaran     = AnggaranPorsi::aktif($tanggal);
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

    public function editMenu(MenuHarian $menuHarian)
    {
        $user = Auth::user();
        if ($user->role === 'admin') {
            abort(403, 'Admin tidak dapat mengedit menu harian.');
        }
        if ($menuHarian->status === 'final') {
            return redirect()->route('menu-harian.show', $menuHarian)
                ->with('error', 'Menu sudah final, tidak bisa diedit.');
        }

        $menuHarian->load('detailBahans.bahanPangan');

        $existingBahans = $menuHarian->detailBahans
            ->filter(fn($d) => $d->bahanPangan)
            ->map(fn($d) => [
                'id'           => $d->bahanPangan->id,
                'kode'         => $d->bahanPangan->kode,
                'nama_bahan'   => $d->bahanPangan->nama_bahan,
                'kategori'     => $d->bahanPangan->kategori,
                'energi'       => $d->bahanPangan->energi,
                'protein'      => $d->bahanPangan->protein,
                'lemak'        => $d->bahanPangan->lemak,
                'karbohidrat'  => $d->bahanPangan->karbohidrat,
                'bdd'          => $d->bahanPangan->bdd,
                'jumlah_gram'  => $d->jumlah_gram,
                'jumlah_porsi' => $d->jumlah_porsi,
            ])->values();

        return view('simulasi.index', compact('menuHarian', 'existingBahans'));
    }

    public function simpan(Request $request)
    {
        if (Auth::user()->role === 'admin') {
            return response()->json(['error' => 'Admin tidak dapat menyimpan menu harian.'], 403);
        }

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
        ]);

        // ── Mode edit: perbarui menu yang sudah ada ─────────────────────────
        if ($request->filled('menu_id')) {
            $menu = MenuHarian::findOrFail($request->menu_id);

            DB::transaction(function () use ($request, $menu) {
                $menu->update([
                    'nama_menu'          => $request->nama_menu,
                    'catatan_anggaran'   => $request->catatan,
                    'jumlah_porsi'       => $request->jumlah_porsi,
                    'anggaran_per_porsi' => AnggaranPorsi::aktif($menu->tanggal->toDateString()),
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

        DB::transaction(function () use ($request) {
            $menu = MenuHarian::create([
                'tanggal'            => $request->tanggal,
                'nama_menu'          => $request->nama_menu,
                'catatan_anggaran'   => $request->catatan,
                'jumlah_porsi'       => $request->jumlah_porsi,
                'anggaran_per_porsi' => AnggaranPorsi::aktif($request->tanggal),
                'status'  => 'draft',
                'user_id' => Auth::id(),
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