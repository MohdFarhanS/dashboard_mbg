<?php
namespace App\Http\Controllers;

use App\Models\HargaBahan;
use App\Models\BahanPangan;
use App\Models\MenuHarian;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class BiayaController extends Controller
{
    // ─── Dashboard Biaya ───────────────────────────────────────────────────────

    public function dashboard(Request $request)
    {
        $user  = Auth::user();
        $bulan = $request->input('bulan', now()->format('Y-m'));
        [$tahun, $bln] = explode('-', $bulan);

        $menus = MenuHarian::with('detailBahans.bahanPangan')
            ->where('unit_sppg', $user->unit_sppg)
            ->whereYear('tanggal', $tahun)
            ->whereMonth('tanggal', $bln)
            ->where('status', 'final')
            ->orderBy('tanggal')
            ->get();

        $rekapBiaya = $menus->map(fn($m) => [
            'menu_id'      => $m->id,
            'tanggal'      => $m->tanggal->format('d/m/Y'),
            'menu'         => $m->nama_menu,
            'biaya'        => $m->totalBiaya(),
        ]);

        // Statistik ringkasan
        $totalHari        = $menus->count();
        $totalBiayaBulan  = $rekapBiaya->sum(fn($r) => $r['biaya']['total_seluruh']);
        $rataCostPorsi    = $totalHari > 0
            ? $rekapBiaya->avg(fn($r) => $r['biaya']['cost_per_porsi'])
            : 0;
        $rataAnggaran     = $totalHari > 0
            ? $rekapBiaya->avg(fn($r) => $r['biaya']['anggaran'])
            : 0;

        // Trend harian untuk Chart.js
        $trendBiaya = $rekapBiaya->map(fn($r) => [
            'tanggal'       => $r['tanggal'],
            'cost_per_porsi'=> $r['biaya']['cost_per_porsi'],
            'anggaran'      => $r['biaya']['anggaran'],
        ])->values();

        // Hitung berapa hari over/under budget
        $overBudget  = $rekapBiaya->filter(fn($r) => $r['biaya']['selisih'] < 0)->count();
        $underBudget = $rekapBiaya->filter(fn($r) => $r['biaya']['selisih'] >= 0)->count();

        return view('biaya.dashboard', compact(
            'bulan', 'rekapBiaya', 'trendBiaya',
            'totalHari', 'totalBiayaBulan', 'rataCostPorsi',
            'rataAnggaran', 'overBudget', 'underBudget'
        ));
    }

    // ─── Manajemen Harga Bahan ─────────────────────────────────────────────────

    public function indexHarga(Request $request)
    {
        $user  = Auth::user();
        $q     = $request->input('q');

        $hargaList = HargaBahan::with('bahanPangan')
            ->where('unit_sppg', $user->unit_sppg)
            ->when($q, fn($query) => $query->whereHas('bahanPangan',
                fn($bq) => $bq->where('nama_bahan', 'like', "%{$q}%")
            ))
            ->orderByDesc('berlaku_mulai')
            ->paginate(20)
            ->withQueryString();

        return view('biaya.harga-index', compact('hargaList', 'q'));
    }

    public function createHarga()
    {
        $bahans = BahanPangan::select('id', 'nama_bahan')->orderBy('nama_bahan')->get();
        return view('biaya.harga-form', compact('bahans'));
    }

    public function storeHarga(Request $request)
    {
        $data = $request->validate([
            'bahan_pangan_id' => 'required|exists:bahan_pangans,id',
            'harga_per_100g'  => 'required|numeric|min:0',
            'berlaku_mulai'   => 'required|date',
            'berlaku_sampai'  => 'nullable|date|after_or_equal:berlaku_mulai',
            'keterangan'      => 'nullable|string|max:200',
        ]);

        $data['unit_sppg'] = Auth::user()->unit_sppg;

        HargaBahan::create($data);

        return redirect()->route('biaya.harga.index')
            ->with('success', 'Harga bahan berhasil disimpan.');
    }

    public function editHarga(HargaBahan $harga)
    {
        $bahans = BahanPangan::select('id', 'nama_bahan')->orderBy('nama_bahan')->get();
        return view('biaya.harga-form', compact('harga', 'bahans'));
    }

    public function updateHarga(Request $request, HargaBahan $harga)
    {
        $data = $request->validate([
            'bahan_pangan_id' => 'required|exists:bahan_pangans,id',
            'harga_per_100g'  => 'required|numeric|min:0',
            'berlaku_mulai'   => 'required|date',
            'berlaku_sampai'  => 'nullable|date|after_or_equal:berlaku_mulai',
            'keterangan'      => 'nullable|string|max:200',
        ]);

        $harga->update($data);

        return redirect()->route('biaya.harga.index')
            ->with('success', 'Harga bahan berhasil diperbarui.');
    }

    public function destroyHarga(HargaBahan $harga)
    {
        $harga->delete();
        return redirect()->route('biaya.harga.index')
            ->with('success', 'Data harga dihapus.');
    }

    // ─── Detail Biaya per Menu ─────────────────────────────────────────────────

    public function detailMenu(MenuHarian $menu)
    {
        $menu->load('detailBahans.bahanPangan');
        $biaya = $menu->totalBiaya();
        return view('biaya.detail-menu', compact('menu', 'biaya'));
    }

    // ─── API: Estimasi biaya real-time (dipakai saat input menu) ──────────────

    public function apiEstimasi(Request $request)
    {
        $user    = Auth::user();
        $tanggal = $request->input('tanggal', today()->toDateString());
        $items   = $request->input('items', []); // [{bahan_pangan_id, jumlah_gram}]
        $porsi   = max((int) $request->input('jumlah_porsi', 1), 1);

        $total = 0;
        $detail = [];

        foreach ($items as $item) {
            $id   = (int) ($item['bahan_pangan_id'] ?? 0);
            $gram = (float) ($item['jumlah_gram'] ?? 0);
            if (!$id || !$gram) continue;

            $harga = HargaBahan::hargaAktif($id, $user->unit_sppg, $tanggal);
            $biaya = ($gram / 100) * $harga;
            $total += $biaya;

            $bahan = BahanPangan::find($id, ['nama_bahan']);
            $detail[] = [
                'bahan_pangan_id' => $id,
                'nama'            => $bahan?->nama_bahan,
                'gram'            => $gram,
                'harga_per_100g'  => $harga,
                'biaya'           => round($biaya, 0),
            ];
        }

        return response()->json([
            'total_seluruh'  => round($total, 0),
            'cost_per_porsi' => round($total / $porsi, 0),
            'detail'         => $detail,
        ]);
    }

    // ─── Set Anggaran per Menu ─────────────────────────────────────────────────

public function editAnggaran(MenuHarian $menu)
{
    return view('biaya.anggaran_form', compact('menu'));
}

public function updateAnggaran(Request $request, MenuHarian $menu)
{
    $data = $request->validate([
        'anggaran_per_porsi' => 'required|numeric|min:1000|max:999999',
        'jumlah_porsi'       => 'required|integer|min:1|max:9999',
        'catatan_anggaran'   => 'nullable|string|max:200',
    ]);

    $menu->update($data);

    return redirect()->route('biaya.dashboard')
        ->with('success', "Anggaran menu \"{$menu->nama_menu}\" berhasil diperbarui.");
}
}
