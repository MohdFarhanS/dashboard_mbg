@extends('layouts.app')
@section('title', 'Menu Harian')

@section('content')
<div class="container-fluid">

    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h4 class="fw-bold mb-0" style="color: var(--primary)">
                <i class="fas fa-utensils me-2"></i>Menu Harian
            </h4>
            <small class="text-muted">Unit: {{ auth()->user()->unit_sppg }}</small>
        </div>
        @if(auth()->user()->role === 'pengelola')
        <a href="{{ route('menu-harian.create') }}" class="btn btn-primary"
           style="background:var(--primary);border-color:var(--primary)">
            <i class="fas fa-plus me-1"></i> Tambah Menu
        </a>
        @endif
    </div>

    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show">
            <i class="fas fa-check-circle me-2"></i>{{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif
    @if(session('error'))
        <div class="alert alert-danger alert-dismissible fade show">
            <i class="fas fa-exclamation-circle me-2"></i>{{ session('error') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    {{-- Filter --}}
    <div class="card border-0 shadow-sm mb-4">
        <div class="card-body">
            <form method="GET" class="row g-2 align-items-end">
                <div class="col-md-4">
                    <label class="form-label small fw-semibold">Bulan</label>
                    <input type="month" name="bulan" class="form-control"
                           value="{{ request('bulan', now()->format('Y-m')) }}">
                </div>
                <div class="col-md-3">
                    <label class="form-label small fw-semibold">Status</label>
                    <select name="status" class="form-select">
                        <option value="">Semua Status</option>
                        <option value="draft" {{ request('status')=='draft'?'selected':'' }}>Draft</option>
                        <option value="final" {{ request('status')=='final'?'selected':'' }}>Final</option>
                    </select>
                </div>
                <div class="col-auto">
                    <button type="submit" class="btn btn-primary"
                            style="background:var(--primary);border-color:var(--primary)">
                        <i class="fas fa-filter me-1"></i> Filter
                    </button>
                </div>
                <div class="col-auto">
                    <a href="{{ route('menu-harian.index') }}" class="btn btn-outline-secondary">Reset</a>
                </div>
            </form>
        </div>
    </div>

    {{-- Tabel --}}
    <div class="card border-0 shadow-sm">
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead style="background:var(--primary-pale)">
                        <tr>
                            <th class="ps-4">Tanggal</th>
                            <th>Nama Menu</th>
                            <th>Jumlah Bahan</th>
                            <th>Estimasi Energi</th>
                            <th>Status</th>
                            <th>Anggaran</th>
                            <th class="text-end pe-4">Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($menus as $menu)
                        @php $gizi = $menu->totalGizi(); @endphp
                        <tr>
                            <td class="ps-4 fw-semibold">
                                {{ $menu->tanggal->translatedFormat('d F Y') }}
                                @if($menu->tanggal->isToday())
                                    <span class="badge ms-1"
                                          style="background:var(--primary);font-size:.65rem">Hari ini</span>
                                @endif
                            </td>
                            <td>{{ $menu->nama_menu ?? '-' }}</td>
                            <td class="text-muted">{{ $menu->detailBahans->count() }} bahan</td>
                            <td>
                                <span class="fw-semibold" style="color:var(--primary)">
                                    {{ number_format($gizi['energi'], 0) }}
                                </span>
                                <span class="text-muted small">kkal</span>
                            </td>
                            <td>
                                @if($menu->status === 'final')
                                    <span class="badge" style="background:#d1e7dd;color:#0a3622">
                                        <i class="fas fa-lock me-1"></i>Final
                                    </span>
                                @else
                                    <span class="badge" style="background:#fff3cd;color:#664d03">
                                        <i class="fas fa-pencil me-1"></i>Draft
                                    </span>
                                @endif
                            </td>
                            <td>
                                @if($menu->status === 'final')
                                    @php $statusAnggaran = $menu->statusAnggaran(); @endphp
                                    @if($statusAnggaran === 'over')
                                        <span class="badge bg-danger">
                                            <i class="fas fa-exclamation-triangle me-1"></i>Over
                                        </span>
                                    @elseif($statusAnggaran === 'warning')
                                        <span class="badge bg-warning text-dark">
                                            <i class="fas fa-exclamation-circle me-1"></i>Mendekati
                                        </span>
                                    @elseif($statusAnggaran === 'aman')
                                        <span class="badge bg-success">
                                            <i class="fas fa-check me-1"></i>Aman
                                        </span>
                                    @else
                                        <span class="text-muted small">-</span>
                                    @endif
                                @else
                                    <span class="text-muted small">—</span>
                                @endif
                            </td>
                            <td class="text-end pe-4">
                                <a href="{{ route('menu-harian.show', $menu) }}"
                                   class="btn btn-sm btn-outline-secondary me-1">
                                    <i class="fas fa-eye"></i>
                                </a>
                                @if(auth()->user()->role === 'pengelola' && $menu->status !== 'final')
                                <a href="{{ route('menu-harian.edit', $menu) }}"
                                   class="btn btn-sm btn-outline-primary me-1">
                                    <i class="fas fa-edit"></i>
                                </a>
                                <form action="{{ route('menu-harian.destroy', $menu) }}" method="POST"
                                      class="d-inline"
                                      onsubmit="return confirm('Hapus menu tanggal ini?')">
                                    @csrf @method('DELETE')
                                    <button class="btn btn-sm btn-outline-danger">
                                        <i class="fas fa-trash"></i>
                                    </button>
                                </form>
                                @endif
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="7" class="text-center text-muted py-5">
                                <i class="fas fa-utensils fa-2x mb-2 d-block opacity-25"></i>
                                Belum ada menu untuk bulan ini.
                                @if(auth()->user()->role === 'pengelola')
                                    <a href="{{ route('menu-harian.create') }}">Tambah sekarang</a>
                                @endif
                            </td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
        @if($menus->hasPages())
        <div class="card-footer bg-white border-0 d-flex justify-content-center">
            {{ $menus->links() }}
        </div>
        @endif
    </div>

</div>
@endsection