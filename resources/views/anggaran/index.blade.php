@extends('layouts.app')

@section('title', 'Kelola Anggaran Porsi')

@section('content')
<div class="container-fluid px-4">
    <div class="d-flex justify-content-between align-items-center mb-3">
        <h4 class="fw-bold text-success mb-0">
            <i class="fas fa-wallet me-2"></i>Kelola Anggaran Per Porsi
        </h4>
        <a href="{{ route('anggaran.create') }}" class="btn btn-success btn-sm">
            <i class="fas fa-plus me-1"></i>Tetapkan Anggaran Baru
        </a>
    </div>

    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show">
            {{ session('success') }}<button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    <div class="card shadow-sm">
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover mb-0">
                    <thead class="table-success">
                        <tr>
                            <th>Unit SPPG</th>
                            <th>Anggaran/Porsi</th>
                            <th>Berlaku Mulai</th>
                            <th>Berlaku Sampai</th>
                            <th>Keterangan</th>
                            <th>Ditetapkan Oleh</th>
                            <th class="text-center">Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($riwayat as $item)
                        <tr>
                            <td><span class="badge bg-success-subtle text-success">{{ $item->unit_sppg }}</span></td>
                            <td class="fw-semibold">Rp {{ number_format($item->anggaran_per_porsi, 0, ',', '.') }}</td>
                            <td>{{ $item->berlaku_mulai->format('d/m/Y') }}</td>
                            <td>
                                @if($item->berlaku_sampai)
                                    {{ $item->berlaku_sampai->format('d/m/Y') }}
                                @else
                                    <span class="text-muted fst-italic">Tidak terbatas</span>
                                @endif
                            </td>                            
                            <td>{{ $item->keterangan ?? '-' }}</td>
                            <td>{{ $item->createdBy->name ?? '-' }}</td>
                            <td class="text-center">
                                <a href="{{ route('anggaran.edit', $item) }}" class="btn btn-warning btn-sm">
                                    <i class="fas fa-edit"></i>
                                </a>
                                <form action="{{ route('anggaran.destroy', $item) }}" method="POST" class="d-inline"
                                      onsubmit="return confirm('Hapus data anggaran ini?')">
                                    @csrf @method('DELETE')
                                    <button class="btn btn-danger btn-sm"><i class="fas fa-trash"></i></button>
                                </form>
                            </td>
                        </tr>
                        @empty
                        <tr><td colspan="7" class="text-center py-4 text-muted">Belum ada data anggaran.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
        @if($riwayat->hasPages())
        <div class="card-footer">{{ $riwayat->links() }}</div>
        @endif
    </div>
</div>
@endsection