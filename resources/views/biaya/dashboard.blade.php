@extends('layouts.app')
@section('title', 'Dashboard Biaya Produksi')

@section('content')
<div class="container-fluid py-4">

    {{-- Header --}}
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h4 class="fw-bold mb-0" style="color:#1a6b3a">
                <i class="fa fa-wallet me-2"></i>Dashboard Biaya Produksi
            </h4>
            <small class="text-muted">Monitoring cost per porsi vs anggaran</small>
        </div>
        <form class="d-flex gap-2">
            <input type="month" name="bulan" value="{{ $bulan }}"
                   class="form-control form-control-sm" onchange="this.form.submit()">
        </form>
    </div>

    {{-- Stat Cards --}}
    <div class="row g-3 mb-4">
        <div class="col-6 col-md-3">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-body">
                    <div class="text-muted small mb-1">Total Biaya Bulan Ini</div>
                    <div class="fw-bold fs-5" style="color:#1a6b3a">
                        Rp {{ number_format($totalBiayaBulan, 0, ',', '.') }}
                    </div>
                    <div class="text-muted small">{{ $totalHari }} hari menu final</div>
                </div>
            </div>
        </div>
        <div class="col-6 col-md-3">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-body">
                    <div class="text-muted small mb-1">Rata-rata Cost/Porsi</div>
                    <div class="fw-bold fs-5" style="color:#1a6b3a">
                        Rp {{ number_format($rataCostPorsi, 0, ',', '.') }}
                    </div>
                    <div class="text-muted small">Rata-rata anggaran: Rp {{ number_format($rataAnggaran, 0, ',', '.') }}</div>
                </div>
            </div>
        </div>
        <div class="col-6 col-md-3">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-body">
                    <div class="text-muted small mb-1">Over Budget</div>
                    <div class="fw-bold fs-5 text-danger">{{ $overBudget }} hari</div>
                    <div class="text-muted small">Cost > anggaran</div>
                </div>
            </div>
        </div>
        <div class="col-6 col-md-3">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-body">
                    <div class="text-muted small mb-1">Under Budget</div>
                    <div class="fw-bold fs-5 text-success">{{ $underBudget }} hari</div>
                    <div class="text-muted small">Cost ≤ anggaran</div>
                </div>
            </div>
        </div>
    </div>

    {{-- Chart Trend --}}
    @if($trendBiaya->count() > 1)
    <div class="card border-0 shadow-sm mb-4">
        <div class="card-header bg-white border-0 fw-semibold">
            <i class="fa fa-chart-line me-1" style="color:#1a6b3a"></i> Trend Cost per Porsi vs Anggaran
        </div>
        <div class="card-body">
            <div style="height:280px"><canvas id="chartTrendBiaya"></canvas></div>
        </div>
    </div>
    @endif

    {{-- DEBUG: hapus setelah fix --}}
    @foreach($rekapBiaya as $r)
    <div>menu_id: {{ $r['menu_id'] ?? 'NULL' }}</div>
    @endforeach

    {{-- Tabel Rekap --}}
    <div class="card border-0 shadow-sm">
        <div class="card-header bg-white border-0 d-flex justify-content-between align-items-center">
            <span class="fw-semibold"><i class="fa fa-table me-1" style="color:#1a6b3a"></i> Rekap Biaya Harian</span>
            <a href="{{ route('biaya.harga.index') }}" class="btn btn-sm btn-outline-success">
                <i class="fa fa-tags me-1"></i>Kelola Harga Bahan
            </a>
        </div>
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover mb-0 align-middle">
                    <thead class="table-light">
                        <tr>
                            <th>Tanggal</th>
                            <th>Menu</th>
                            <th class="text-end">Porsi</th>
                            <th class="text-end">Cost/Porsi</th>
                            <th class="text-end">Anggaran</th>
                            <th class="text-end">Selisih</th>
                            <th class="text-center">Status</th>
                            <th></th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($rekapBiaya as $r)
                        @php
                            $b = $r['biaya'];
                            $over = $b['selisih'] < 0;
                        @endphp
                        <tr>
                            <td>{{ $r['tanggal'] }}</td>
                            <td>{{ $r['menu'] }}</td>
                            <td class="text-end">{{ number_format($b['jumlah_porsi']) }}</td>
                            <td class="text-end fw-semibold">Rp {{ number_format($b['cost_per_porsi'], 0, ',', '.') }}</td>
                            <td class="text-end">Rp {{ number_format($b['anggaran'], 0, ',', '.') }}</td>
                            <td class="text-end {{ $over ? 'text-danger' : 'text-success' }}">
                                {{ $over ? '-' : '+' }}Rp {{ number_format(abs($b['selisih']), 0, ',', '.') }}
                            </td>
                            <td class="text-center">
                                @if($b['anggaran'] == 0)
                                    <span class="badge bg-secondary">Belum diset</span>
                                @elseif($over)
                                    <span class="badge bg-danger">Over</span>
                                @else
                                    <span class="badge bg-success">Aman</span>
                                @endif
                            </td>
                            <td>
                                <div class="d-flex gap-1">
                                    <a href="{{ route('biaya.detail-menu', $r['menu_id']) }}"
                                       class="btn btn-sm btn-outline-secondary py-0 px-2">
                                        <i class="fa fa-eye me-1"></i>Detail
                                    </a>
                                    <a href="{{ route('biaya.edit-anggaran', $r['menu_id']) }}"
                                       class="btn btn-sm btn-outline-success py-0 px-2">
                                        <i class="fa fa-wallet me-1"></i>Anggaran
                                    </a>
                                </div>
                            </td>
                        </tr>
                        @empty
                        <tr><td colspan="8" class="text-center text-muted py-4">
                            Belum ada data menu final bulan ini.
                        </td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>

</div>
@endsection

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.min.js"></script>
<script>
@if($trendBiaya->count() > 1)
const trendBiaya = @json($trendBiaya);
new Chart(document.getElementById('chartTrendBiaya'), {
    type: 'line',
    data: {
        labels: trendBiaya.map(d => d.tanggal),
        datasets: [
            {
                label: 'Cost per Porsi (Rp)',
                data: trendBiaya.map(d => d.cost_per_porsi),
                borderColor: '#1a6b3a',
                backgroundColor: 'rgba(26,107,58,.1)',
                fill: true,
                tension: 0.3,
                pointRadius: 4,
                pointBackgroundColor: '#1a6b3a',
            },
            {
                label: 'Anggaran (Rp)',
                data: trendBiaya.map(d => d.anggaran),
                borderColor: '#dc3545',
                borderDash: [6,3],
                borderWidth: 2,
                pointRadius: 0,
                fill: false,
            }
        ]
    },
    options: {
        responsive: true,
        maintainAspectRatio: false,
        plugins: {
            legend: { position: 'top' },
            tooltip: {
                callbacks: {
                    label: ctx => ` ${ctx.dataset.label}: Rp ${ctx.parsed.y.toLocaleString('id-ID')}`
                }
            }
        },
        scales: {
            y: {
                beginAtZero: false,
                ticks: { callback: v => 'Rp ' + v.toLocaleString('id-ID') },
                grid: { color: 'rgba(0,0,0,.05)' }
            },
            x: { grid: { display: false } }
        }
    }
});
@endif
</script>
@endpush