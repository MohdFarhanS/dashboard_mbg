<?php

namespace App\Http\Controllers;

use App\Models\BahanPangan;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class BahanPanganController extends Controller
{
    // 13 kategori TKPI
    const KATEGORI_LIST = [
        'Serealia', 'Umbi', 'Kacang', 'Sayuran', 'Buah',
        'Daging', 'Ikan', 'Telur', 'Susu', 'Lemak',
        'Gula', 'Bumbu', 'Minuman',
    ];

    public function __construct()
    {
        $this->middleware('auth');
    }

    /**
     * Daftar bahan pangan dengan search & filter
     */
    public function index(Request $request)
    {
        $query = BahanPangan::query();

        // Search
        if ($request->filled('cari')) {
            $cari = $request->cari;
            $query->where(function ($q) use ($cari) {
                $q->where('nama_bahan', 'like', "%{$cari}%")
                  ->orWhere('kode', 'like', "%{$cari}%");
            });
        }

        // Filter kategori
        if ($request->filled('kategori')) {
            $query->where('kategori', $request->kategori);
        }

        // Filter sub_kategori
        if ($request->filled('sub_kategori')) {
            $query->where('sub_kategori', $request->sub_kategori);
        }

        // Sort
        $sortBy  = $request->get('sort', 'kode');
        $sortDir = $request->get('dir', 'asc');
        $allowed = ['kode', 'nama_bahan', 'kategori', 'energi', 'protein', 'lemak', 'karbohidrat'];
        if (!in_array($sortBy, $allowed)) $sortBy = 'kode';
        if (!in_array($sortDir, ['asc', 'desc'])) $sortDir = 'asc';

        $bahanPangans = $query->orderBy($sortBy, $sortDir)->paginate(20)->withQueryString();

        // Stats untuk info panel
        $stats = [
            'total'       => BahanPangan::count(),
            'per_kategori' => BahanPangan::selectRaw('kategori, COUNT(*) as total')
                                ->groupBy('kategori')
                                ->orderBy('kategori')
                                ->pluck('total', 'kategori'),
        ];

        return view('bahan-pangan.index', compact('bahanPangans', 'stats'));
    }

    /**
     * Form tambah bahan pangan
     */
    public function create()
    {
        $this->checkAdminRole();
        return view('bahan-pangan.form', [
            'bahan'         => null,
            'kategoriList'  => self::KATEGORI_LIST,
            'title'         => 'Tambah Bahan Pangan',
        ]);
    }

    /**
     * Simpan bahan pangan baru
     */
    public function store(Request $request)
    {
        $this->checkAdminRole();

        $validated = $this->validateBahan($request);

        BahanPangan::create($validated);

        return redirect()->route('bahan-pangan.index')
            ->with('success', "Bahan pangan <strong>{$validated['nama_bahan']}</strong> berhasil ditambahkan.");
    }

    /**
     * Detail bahan pangan
     */
    public function show(BahanPangan $bahanPangan)
    {
        return view('bahan-pangan.show', compact('bahanPangan'));
    }

    /**
     * Form edit
     */
    public function edit(BahanPangan $bahanPangan)
    {
        $this->checkAdminRole();
        return view('bahan-pangan.form', [
            'bahan'        => $bahanPangan,
            'kategoriList' => self::KATEGORI_LIST,
            'title'        => 'Edit Bahan Pangan',
        ]);
    }

    /**
     * Update bahan pangan
     */
    public function update(Request $request, BahanPangan $bahanPangan)
    {
        $this->checkAdminRole();

        $validated = $this->validateBahan($request, $bahanPangan->id);
        $bahanPangan->update($validated);

        return redirect()->route('bahan-pangan.index')
            ->with('success', "Data <strong>{$bahanPangan->nama_bahan}</strong> berhasil diperbarui.");
    }

    /**
     * Hapus bahan pangan
     */
    public function destroy(BahanPangan $bahanPangan)
    {
        $this->checkAdminRole();

        $nama = $bahanPangan->nama_bahan;
        $bahanPangan->delete();

        return redirect()->route('bahan-pangan.index')
            ->with('success', "Bahan pangan <strong>{$nama}</strong> berhasil dihapus.");
    }

    /**
     * Toggle status aktif
     */
    public function toggleStatus(BahanPangan $bahanPangan)
    {
        $this->checkAdminRole();

        $bahanPangan->update(['is_active' => !$bahanPangan->is_active]);
        $status = $bahanPangan->is_active ? 'diaktifkan' : 'dinonaktifkan';

        return back()->with('success', "Bahan pangan berhasil {$status}.");
    }

    /**
     * API: Search untuk Select2 / autocomplete
     */
    public function apiSearch(Request $request)
    {
        $q     = $request->input('q', '');
        $limit = $request->input('limit', 8);

        $results = BahanPangan::where('is_active', true)
            ->where(function ($query) use ($q) {
                $query->where('nama_bahan', 'like', "%{$q}%")
                    ->orWhere('kode', 'like', "%{$q}%");
            })
            ->select([
                'id', 'kode', 'nama_bahan', 'kategori',
                'energi', 'protein', 'lemak', 'karbohidrat', 'bdd'
            ])
            ->limit($limit)
            ->get()
            ->map(function ($item) {
                // Pastikan bdd tidak null
                $item->bdd = $item->bdd ?? 100;
                return $item;
            });

        return response()->json($results);
    }

    /**
     * Validasi input bahan pangan
     */
    private function validateBahan(Request $request, ?int $ignoreId = null): array
    {
        $rules = [
            'kode'          => "required|string|max:10|unique:bahan_pangans,kode" . ($ignoreId ? ",{$ignoreId}" : ''),
            'kode_lama'     => 'nullable|string|max:10',
            'nama_bahan'    => 'required|string|max:255',
            'kategori'      => 'required|in:' . implode(',', self::KATEGORI_LIST),
            'sub_kategori'  => 'nullable|in:TUNGGAL,OLAHAN',
            'sumber'        => 'nullable|string|max:50',
            'is_active'     => 'boolean',
        ];

        // Semua kolom nutrisi: nullable|numeric
        $nutrisiCols = [
            'bdd','air','energi','protein','lemak','karbohidrat','serat','abu',
            'kalsium','fosfor','besi','natrium','kalium','tembaga','seng',
            'retinol','b_karoten','kar_total','thiamin','riboflavin','niasin','vit_c',
        ];
        foreach ($nutrisiCols as $col) {
            $rules[$col] = 'nullable|numeric|min:0';
        }

        return $request->validate($rules);
    }

    /**
     * Helper untuk authorize admin saja
     */
    private function checkAdminRole(): void
    {
        if (auth()->user()->role !== 'admin') {
            abort(403, 'Akses ditolak. Fitur ini hanya untuk admin.');
        }
    }
}