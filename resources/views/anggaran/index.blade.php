@extends('layouts.app')

@section('title', 'Kelola Anggaran Porsi')

@section('content')
<div class="container-fluid px-4">
    <div class="d-flex justify-content-between align-items-center mb-3">
        <h4 class="fw-bold mb-0" style="color:#0f4c81">
            <i class="fas fa-wallet me-2"></i>Kelola Anggaran Per Porsi
        </h4>
        <a href="{{ route('anggaran.create') }}" class="btn btn-primary btn-sm">
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
                    <thead class="table-primary">
                        <tr>
                            <th>Anggaran/Porsi</th>
                            <th>Berlaku Mulai</th>
                            <th>Keterangan</th>
                            <th>Ditetapkan Oleh</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($riwayat as $item)
                        <tr>
                            <td class="fw-semibold">Rp {{ number_format($item->anggaran_per_porsi, 0, ',', '.') }}</td>
                            <td>{{ $item->berlaku_mulai->format('d/m/Y') }}</td>
                            <td>{{ $item->keterangan ?? '-' }}</td>
                            <td>{{ $item->createdBy->name ?? '-' }}</td>
                        </tr>
                        @empty
                        <tr><td colspan="4" class="text-center py-4 text-muted">Belum ada data anggaran.</td></tr>
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