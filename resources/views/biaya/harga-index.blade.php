@extends('layouts.app')
@section('title', 'Kelola Harga Bahan')

@section('content')
<div class="container-fluid py-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h4 class="fw-bold mb-0" style="color:#1a6b3a">
            <i class="fa fa-tags me-2"></i>Kelola Harga Bahan
        </h4>
        <a href="{{ route('biaya.harga.create') }}" class="btn btn-success btn-sm">
            <i class="fa fa-plus me-1"></i>Tambah Harga
        </a>
    </div>

    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show">
            {{ session('success') }}<button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    {{-- Search --}}
    <form class="mb-3">
        <div class="input-group" style="max-width:360px">
            <input type="text" name="q" value="{{ $q }}" class="form-control"
                   placeholder="Cari nama bahan...">
            <button class="btn btn-outline-success" type="submit"><i class="fa fa-search"></i></button>
        </div>
    </form>

    <div class="card border-0 shadow-sm">
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover mb-0 align-middle">
                    <thead class="table-light">
                        <tr>
                            <th>#</th>
                            <th>Nama Bahan</th>
                            <th class="text-end">Harga per 100g</th>
                            <th>Berlaku Mulai</th>
                            <th>Berlaku Sampai</th>
                            <th>Keterangan</th>
                            <th class="text-center">Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($hargaList as $h)
                        <tr>
                            <td>{{ $hargaList->firstItem() + $loop->index }}</td>
                            <td>{{ $h->bahanPangan?->nama_bahan ?? '-' }}</td>
                            <td class="text-end">Rp {{ number_format($h->harga_per_100g, 0, ',', '.') }}</td>
                            <td>{{ $h->berlaku_mulai->format('d/m/Y') }}</td>
                            <td>{{ $h->berlaku_sampai ? $h->berlaku_sampai->format('d/m/Y') : '—' }}</td>
                            <td>{{ $h->keterangan ?? '—' }}</td>
                            <td class="text-center">
                                <a href="{{ route('biaya.harga.edit', $h) }}"
                                   class="btn btn-xs btn-outline-warning btn-sm py-0 px-2 me-1">Edit</a>
                                <form action="{{ route('biaya.harga.destroy', $h) }}" method="POST"
                                      class="d-inline" onsubmit="return confirm('Hapus data ini?')">
                                    @csrf @method('DELETE')
                                    <button class="btn btn-xs btn-outline-danger btn-sm py-0 px-2">Hapus</button>
                                </form>
                            </td>
                        </tr>
                        @empty
                        <tr><td colspan="7" class="text-center text-muted py-4">
                            Belum ada data harga bahan.
                        </td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            <div class="p-3">{{ $hargaList->links() }}</div>
        </div>
    </div>
</div>
@endsection