@extends('layouts.app')
@section('title', 'Detail Menu')

@section('content')
<div class="container-fluid">

    <div class="d-flex align-items-center justify-content-between mb-4">
        <div class="d-flex align-items-center">
            <a href="{{ route('menu-harian.index') }}" class="btn btn-sm btn-outline-secondary me-3">
                <i class="fas fa-arrow-left"></i>
            </a>
            <div>
                <h4 class="fw-bold mb-0" style="color:var(--primary)">
                    Detail Menu — {{ $menuHarian->tanggal->translatedFormat('d F Y') }}
                </h4>
                <small class="text-muted">
                    {{ $menuHarian->unit_sppg }}
                    @if($menuHarian->nama_menu)
                        · <span class="fw-semibold">{{ $menuHarian->nama_menu }}</span>
                    @endif
                </small>
            </div>
        </div>
        <div class="d-flex gap-2 align-items-center">
            @if($menuHarian->status === 'final')
                <span class="badge p-2" style="background:#d1e7dd;color:#0a3622;font-size:.85rem">
                    <i class="fas fa-lock me-1"></i>Final
                </span>
            @else
                <span class="badge p-2" style="background:#fff3cd;color:#664d03;font-size:.85rem">
                    <i class="fas fa-pencil me-1"></i>Draft
                </span>
                @if(auth()->user()->role === 'pengelola')
                <a href="{{ route('menu-harian.edit', $menuHarian) }}" class="btn btn-outline-primary btn-sm">
                    <i class="fas fa-edit me-1"></i>Edit
                </a>
                <form action="{{ route('menu-harian.finalize', $menuHarian) }}" method="POST"
                      onsubmit="return confirm('Finalisasi menu? Tidak bisa diedit setelah ini.')">
                    @csrf @method('PATCH')
                    <button class="btn btn-sm btn-success"
                            style="background:var(--primary);border-color:var(--primary)">
                        <i class="fas fa-lock me-1"></i>Finalisasi
                    </button>
                </form>
                @endif
            @endif
        </div>
    </div>

    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show">
            <i class="fas fa-check-circle me-2"></i>{{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    @php $gizi = $menuHarian->totalGizi(); @endphp

    {{-- Stat gizi --}}
    <div class="row g-3 mb-4">
        @foreach([
            ['Energi',      'energi',      'kkal', '#1a6b3a'],
            ['Protein',     'protein',     'g',    '#0d6efd'],
            ['Lemak',       'lemak',       'g',    '#fd7e14'],
            ['Karbohidrat', 'karbohidrat', 'g',    '#6f42c1'],
            ['Serat',       'serat',       'g',    '#20c997'],
            ['Vit. C',      'vit_c',       'mg',   '#dc3545'],
        ] as [$label, $key, $unit, $color])
        <div class="col-6 col-md-2">
            <div class="card border-0 shadow-sm text-center h-100">
                <div class="card-body py-3 px-2">
                    <div class="fw-bold fs-5" style="color:{{ $color }}">
                        {{ number_format($gizi[$key], 1) }}
                    </div>
                    <div class="text-muted" style="font-size:.75rem">{{ $label }} ({{ $unit }})</div>
                </div>
            </div>
        </div>
        @endforeach
    </div>

    {{-- ============================================================ --}}
    {{-- BUDGET ALERT — tampil di antara stat gizi dan tabel bahan    --}}
    {{-- ============================================================ --}}
    @php
        $biaya          = $menuHarian->totalBiaya();
        $statusAnggaran = $menuHarian->statusAnggaran();
        $persenAnggaran = $biaya['persen_anggaran'];
        $anggaranAktif  = $biaya['anggaran'];
        $costPerPorsi   = $biaya['cost_per_porsi'];
        $selisih        = $biaya['selisih'];
    @endphp

    @if($statusAnggaran !== 'belum_ada_data')
    <div class="card border-0 shadow-sm mb-4">
        <div class="card-body py-3 px-4">

            {{-- Header row --}}
            <div class="d-flex justify-content-between align-items-center mb-2">
                <div class="d-flex align-items-center gap-2">
                    <i class="fas fa-wallet" style="color:var(--primary)"></i>
                    <span class="fw-semibold">Realisasi Biaya vs Anggaran Per Porsi</span>
                    @if($statusAnggaran === 'over')
                        <span class="badge bg-danger">
                            <i class="fas fa-exclamation-triangle me-1"></i>Melebihi Anggaran
                        </span>
                    @elseif($statusAnggaran === 'warning')
                        <span class="badge bg-warning text-dark">
                            <i class="fas fa-exclamation-circle me-1"></i>Mendekati Batas
                        </span>
                    @else
                        <span class="badge bg-success">
                            <i class="fas fa-check me-1"></i>Dalam Anggaran
                        </span>
                    @endif
                </div>
                <div class="text-end">
                    <span class="fw-bold fs-5
                        @if($statusAnggaran === 'over') text-danger
                        @elseif($statusAnggaran === 'warning') text-warning
                        @else text-success @endif">
                        Rp {{ number_format($costPerPorsi, 0, ',', '.') }}
                    </span>
                    <span class="text-muted small"> / Rp {{ number_format($anggaranAktif, 0, ',', '.') }}</span>
                </div>
            </div>

            {{-- Progress bar --}}
            <div class="progress mb-2" style="height:10px;border-radius:8px;">
                <div class="progress-bar
                    @if($statusAnggaran === 'over') bg-danger
                    @elseif($statusAnggaran === 'warning') bg-warning
                    @else bg-success @endif"
                    role="progressbar"
                    style="width:{{ min($persenAnggaran, 100) }}%;border-radius:8px;"
                    aria-valuenow="{{ $persenAnggaran }}"
                    aria-valuemin="0" aria-valuemax="100">
                </div>
            </div>

            {{-- Label bawah progress --}}
            <div class="d-flex justify-content-between">
                <small class="text-muted">Rp 0</small>
                <small class="fw-semibold
                    @if($statusAnggaran === 'over') text-danger
                    @elseif($statusAnggaran === 'warning') text-warning
                    @else text-success @endif">
                    {{ $persenAnggaran }}% dari anggaran
                </small>
                <small class="text-muted">Rp {{ number_format($anggaranAktif, 0, ',', '.') }}</small>
            </div>

        </div>
    </div>

    {{-- Alert banner over / warning --}}
    @if($statusAnggaran === 'over')
    <div class="alert alert-danger d-flex align-items-start gap-3 mb-4">
        <i class="fas fa-exclamation-triangle fs-5 mt-1 flex-shrink-0"></i>
        <div>
            <div class="fw-semibold mb-1">Biaya per porsi melebihi anggaran!</div>
            Biaya saat ini <strong>Rp {{ number_format($costPerPorsi, 0, ',', '.') }}</strong>
            melebihi anggaran sebesar
            <strong class="text-danger">Rp {{ number_format(abs($selisih), 0, ',', '.') }}</strong>.
            Pertimbangkan untuk mengurangi porsi atau mengganti bahan.
        </div>
    </div>
    @elseif($statusAnggaran === 'warning')
    <div class="alert alert-warning d-flex align-items-start gap-3 mb-4">
        <i class="fas fa-info-circle fs-5 mt-1 flex-shrink-0"></i>
        <div>
            <div class="fw-semibold mb-1">Mendekati batas anggaran.</div>
            Sisa anggaran per porsi:
            <strong>Rp {{ number_format($selisih, 0, ',', '.') }}</strong>
            ({{ round(100 - $persenAnggaran, 1) }}% tersisa).
        </div>
    </div>
    @endif
    @endif
    {{-- ============================================================ --}}

    {{-- Tabel bahan --}}
    <div class="card border-0 shadow-sm">
        <div class="card-header border-0 d-flex justify-content-between align-items-center"
             style="background:#d1e7dd">
            <span class="fw-semibold" style="color:#0a3622">
                <i class="fas fa-cloud-sun me-2"></i>Bahan Pangan — Makan Siang
            </span>
            <small class="text-muted">{{ $menuHarian->detailBahans->count() }} bahan</small>
        </div>
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead class="table-light">
                        <tr>
                            <th class="ps-4">Bahan Pangan</th>
                            <th>Kategori</th>
                            <th class="text-end">Gram</th>
                            <th class="text-end">Porsi</th>
                            <th class="text-end">BDD</th>
                            <th class="text-end">Energi</th>
                            <th class="text-end">Protein</th>
                            <th class="text-end pe-4">Lemak</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($menuHarian->detailBahans as $detail)
                        @php
                            $b = $detail->bahanPangan;
                            $f = ($detail->jumlah_gram * ($b->bdd / 100)) / 100 * $detail->jumlah_porsi;
                        @endphp
                        <tr>
                            <td class="ps-4">
                                <div class="fw-semibold">{{ $b->nama_bahan }}</div>
                                <div class="text-muted" style="font-size:.72rem">{{ $b->kode }}</div>
                            </td>
                            <td><small class="text-muted">{{ $b->kategori }}</small></td>
                            <td class="text-end">{{ $detail->jumlah_gram }}g</td>
                            <td class="text-end">{{ $detail->jumlah_porsi }}x</td>
                            <td class="text-end text-muted small">{{ $b->bdd }}%</td>
                            <td class="text-end fw-semibold" style="color:var(--primary)">
                                {{ number_format($f * ($b->energi ?? 0), 1) }}
                            </td>
                            <td class="text-end">{{ number_format($f * ($b->protein ?? 0), 2) }}</td>
                            <td class="text-end pe-4">{{ number_format($f * ($b->lemak ?? 0), 2) }}</td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="8" class="text-center text-muted py-4">Belum ada bahan.</td>
                        </tr>
                        @endforelse
                    </tbody>
                    @if($menuHarian->detailBahans->count())
                    <tfoot style="background:var(--primary-pale)">
                        <tr class="fw-semibold">
                            <td class="ps-4" colspan="5">Total Gizi</td>
                            <td class="text-end" style="color:var(--primary)">
                                {{ number_format($gizi['energi'], 1) }} kkal
                            </td>
                            <td class="text-end">{{ number_format($gizi['protein'], 2) }} g</td>
                            <td class="text-end pe-4">{{ number_format($gizi['lemak'], 2) }} g</td>
                        </tr>
                    </tfoot>
                    @endif
                </table>
            </div>
        </div>
        @if($menuHarian->catatan)
        <div class="card-footer bg-white border-top">
            <small class="text-muted"><i class="fas fa-sticky-note me-1"></i>{{ $menuHarian->catatan }}</small>
        </div>
        @endif
    </div>

</div>
@endsection