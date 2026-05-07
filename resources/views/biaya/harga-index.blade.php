{{-- resources/views/biaya/harga-index.blade.php --}}
@extends('layouts.app')

@section('title', 'Manajemen Harga Bahan')

@section('content')
<div class="container-fluid py-4">

    <div class="d-flex justify-content-between align-items-center mb-4">
        <div class="d-flex align-items-center">
            <a href="{{ route('biaya.dashboard') }}" class="btn btn-sm btn-outline-secondary me-3">
                <i class="fas fa-arrow-left"></i>
            </a>
    
            <h4 class="fw-semibold mb-0">
                <i class="fa fa-tags me-0" style="color:#0f4c81"></i>
                Manajemen Harga Bahan
            </h4>
        </div>
    
        <!-- Kanan: tombol tambah -->
        @if(Auth::user()->isAkuntan())
        <a href="{{ route('biaya.harga.create') }}" class="btn btn-primary btn-sm">
            <i class="fa fa-plus me-1"></i>Tambah Harga
        </a>
        @endif
    </div>

    {{-- Search --}}
    <form method="GET" class="mb-3 d-flex gap-2">
        <input type="text" name="q" value="{{ $q }}"
               class="form-control form-control-sm" style="max-width:280px"
               placeholder="Cari nama bahan...">
        <button type="submit" class="btn btn-outline-primary btn-sm">
            <i class="fa fa-search"></i>
        </button>
        @if($q)
            <a href="{{ route('biaya.harga.index') }}" class="btn btn-outline-secondary btn-sm">Reset</a>
        @endif
    </form>

    <div class="card border-0 shadow-sm">
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover mb-0">
                    <thead class="table-light">
                        <tr>
                            <th>Nama Bahan</th>
                            <th class="text-end">Harga / kg</th>
                            <th class="text-end">Harga / gram</th>
                            <th>Berlaku Mulai</th>
                            <th>Keterangan</th>
                            <th class="text-center">Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($hargaList as $h)
                        <tr>
                            <td>{{ $h->bahanPangan?->nama_bahan ?? '—' }}</td>
                            <td class="text-end">Rp {{ number_format($h->harga_per_100g * 10, 0, ',', '.') }}</td>
                            <td class="text-end text-muted small">Rp {{ number_format($h->harga_per_100g / 100, 2, ',', '.') }}</td>
                            <td>{{ $h->berlaku_mulai->format('d/m/Y') }}</td>
                            <td class="text-muted small">{{ $h->keterangan ?? '—' }}</td>
                            <td class="text-center">
                                <div class="d-flex gap-1 justify-content-center">
                                    <a href="{{ route('biaya.harga.edit', $h) }}"
                                       class="btn btn-sm btn-outline-primary py-0 px-2">
                                        <i class="fa fa-pencil"></i>
                                    </a>
                                    <form method="POST" action="{{ route('biaya.harga.destroy', $h) }}"
                                          onsubmit="return confirm('Hapus data harga ini?')">
                                        @csrf @method('DELETE')
                                        <button class="btn btn-sm btn-outline-danger py-0 px-2">
                                            <i class="fa fa-trash"></i>
                                        </button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="6" class="text-center text-muted py-4">
                                Belum ada data harga bahan.
                            </td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <div class="mt-3">
        {{ $hargaList->links() }}
    </div>

</div>
@endsection