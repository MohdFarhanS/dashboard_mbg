@extends('layouts.app')
@section('title', 'Riwayat Anggaran Per Porsi')

@section('content')
<div class="container-fluid py-4">

    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h4 class="fw-bold mb-0" style="color:#1a6b3a">
                <i class="fas fa-wallet me-2"></i>Riwayat Anggaran Per Porsi
            </h4>
            <small class="text-muted">Riwayat penetapan anggaran MBG per unit SPPG</small>
        </div>
        <a href="{{ route('anggaran.create') }}" class="btn btn-success btn-sm">
            <i class="fas fa-plus me-1"></i>Tetapkan Anggaran Baru
        </a>
    </div>

    @if(session('success'))
    <div class="alert alert-success alert-dismissible fade show">
        <i class="fas fa-check-circle me-2"></i>{{ session('success') }}
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
    @endif

    {{-- Anggaran Aktif Saat Ini --}}
    <div class="card border-0 shadow-sm mb-4" style="border-left: 4px solid #1a6b3a !important;">
        <div class="card-header border-0 fw-semibold" style="background:#d1e7dd; color:#0a3622">
            <i class="fas fa-circle-check me-2"></i>Anggaran Aktif Hari Ini — {{ now()->translatedFormat('d F Y') }}
        </div>
        <div class="card-body">
            @php
                $units = $riwayat->groupBy('unit_sppg')->keys();
            @endphp
            @forelse($units as $unit)
            @php
                $aktif = \App\Models\AnggaranPorsi::aktif($unit);
            @endphp
            <div class="d-flex justify-content-between align-items-center py-2 border-bottom">
                <div>
                    <span class="fw-semibold">{{ $unit }}</span>
                </div>
                <div class="text-end">
                    <span class="fw-bold fs-5" style="color:#1a6b3a">
                        Rp {{ number_format($aktif, 0, ',', '.') }}
                    </span>
                    <span class="text-muted small">/porsi</span>
                </div>
            </div>
            @empty
            <p class="text-muted mb-0">Belum ada anggaran ditetapkan.</p>
            @endforelse
        </div>
    </div>

    {{-- Tabel Riwayat --}}
    <div class="card border-0 shadow-sm">
        <div class="card-header bg-white border-bottom fw-semibold">
            <i class="fas fa-history me-2" style="color:#1a6b3a"></i>Riwayat Perubahan Anggaran
        </div>
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead class="table-light">
                        <tr>
                            <th class="ps-4">Unit SPPG</th>
                            <th class="text-end">Anggaran/Porsi</th>
                            <th>Berlaku Mulai</th>
                            <th>Berlaku Sampai</th>
                            <th>Keterangan</th>
                            <th>Ditetapkan Oleh</th>
                            <th class="text-center">Status</th>
                            <th class="text-center pe-4">Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($riwayat as $r)
                        @php
                            $today     = today();
                            $mulai     = $r->berlaku_mulai;
                            $sampai    = $r->berlaku_sampai;
                            $isAktif   = $mulai->lte($today) && ($sampai === null || $sampai->gte($today));
                            $isExpired = $sampai !== null && $sampai->lt($today);
                            $isFuture  = $mulai->gt($today);
                        @endphp
                        <tr>
                            <td class="ps-4 fw-semibold">{{ $r->unit_sppg }}</td>
                            <td class="text-end fw-bold" style="color:#1a6b3a">
                                Rp {{ number_format($r->anggaran_per_porsi, 0, ',', '.') }}
                            </td>
                            <td>{{ $r->berlaku_mulai->format('d/m/Y') }}</td>
                            <td>
                                {{ $r->berlaku_sampai ? $r->berlaku_sampai->format('d/m/Y') : '—' }}
                            </td>
                            <td class="text-muted small">{{ $r->keterangan ?? '—' }}</td>
                            <td class="small">{{ $r->createdBy->name ?? '—' }}</td>
                            <td class="text-center">
                                @if($isAktif)
                                    <span class="badge" style="background:#d1e7dd; color:#0a3622">
                                        <i class="fas fa-circle-check me-1"></i>Aktif
                                    </span>
                                @elseif($isFuture)
                                    <span class="badge" style="background:#fff3cd; color:#664d03">
                                        <i class="fas fa-clock me-1"></i>Akan Datang
                                    </span>
                                @else
                                    <span class="badge bg-secondary">
                                        <i class="fas fa-history me-1"></i>Kadaluarsa
                                    </span>
                                @endif
                            </td>
                            <td class="text-center pe-4">
                                <a href="{{ route('anggaran.edit', $r) }}"
                                   class="btn btn-sm btn-outline-warning">
                                    <i class="fas fa-edit"></i>
                                </a>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="8" class="text-center text-muted py-5">
                                <i class="fas fa-wallet fa-2x mb-2 d-block opacity-25"></i>
                                Belum ada riwayat anggaran.
                            </td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
        @if($riwayat->hasPages())
        <div class="card-footer bg-white border-0 d-flex justify-content-center">
            {{ $riwayat->links('pagination::bootstrap-5') }}
        </div>
        @endif
    </div>
</div>
@endsection