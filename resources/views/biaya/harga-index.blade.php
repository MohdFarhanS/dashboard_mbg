{{-- resources/views/biaya/harga-index.blade.php --}}
@extends('layouts.app')

@section('title', 'Manajemen Harga Bahan')

@section('content')
<div class="container-fluid py-4">

    <div class="d-flex justify-content-between align-items-center mb-4">
        <h4 class="fw-semibold mb-0">
            <i class="fa fa-tags text-success me-2"></i>Manajemen Harga Bahan
        </h4>
        <a href="{{ route('biaya.harga.create') }}" class="btn btn-success btn-sm">
            <i class="fa fa-plus me-1"></i>Tambah Harga
        </a>
    </div>

    {{-- Search --}}
    <form method="GET" class="mb-3 d-flex gap-2">
        <input type="text" name="q" value="{{ $q }}"
               class="form-control form-control-sm" style="max-width:280px"
               placeholder="Cari nama bahan...">
        <button type="submit" class="btn btn-outline-success btn-sm">
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
                            {{-- FIX: Kolom Unit hanya untuk admin --}}
                            @if(auth()->user()->role === 'admin')
                            <th>Unit SPPG</th>
                            @endif
                            <th class="text-end">Harga / 100g</th>
                            <th>Berlaku Mulai</th>
                            <th>Berlaku Sampai</th>
                            <th>Keterangan</th>
                            <th class="text-center">Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($hargaList as $h)
                        <tr>
                            <td>{{ $h->bahanPangan?->nama_bahan ?? '—' }}</td>
                            {{-- FIX: Tampilkan unit untuk admin --}}
                            @if(auth()->user()->role === 'admin')
                            <td><span class="badge bg-secondary-subtle text-secondary">{{ $h->unit_sppg }}</span></td>
                            @endif
                            <td class="text-end">Rp {{ number_format($h->harga_per_100g, 0, ',', '.') }}</td>
                            <td>{{ $h->berlaku_mulai->format('d/m/Y') }}</td>
                            <td>{{ $h->berlaku_sampai?->format('d/m/Y') ?? '—' }}</td>
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
                            <td colspan="{{ auth()->user()->role === 'admin' ? 7 : 6 }}" class="text-center text-muted py-4">
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