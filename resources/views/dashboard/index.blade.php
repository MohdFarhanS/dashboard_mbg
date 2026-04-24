@extends('layouts.app')

@section('title', 'Dashboard — MBG')
@section('page-title', 'Dashboard')

@section('content')

{{-- Greeting --}}
<div class="mb-4">
    <h4 class="fw-700 mb-1" style="color:#1a2e1d;">
        Selamat datang, {{ Auth::user()->nama_lengkap ?? Auth::user()->name }} 👋
    </h4>
    <p class="text-muted mb-0" style="font-size:.85rem;">
        Berikut ringkasan monitoring gizi dan biaya produksi menu hari ini.
    </p>
</div>

{{-- STAT CARDS --}}
<div class="row g-3 mb-4">

    {{-- Total Menu --}}
    <div class="col-6 col-lg-3">
        <div class="stat-card">
            <div class="stat-icon" style="background:#e8f5ee;">🍽️</div>
            <div>
                <div class="stat-label">Menu Hari Ini</div>
                <div class="stat-value">{{ $stats['total_menu_hari_ini'] }}</div>
                <div class="stat-sub">sesi makan</div>
            </div>
        </div>
    </div>

    {{-- Total Kalori vs Target --}}
    <div class="col-6 col-lg-3">
        <div class="stat-card">
            <div class="stat-icon" style="background:#fff3e0;">🔥</div>
            <div>
                <div class="stat-label">Total Kalori</div>
                <div class="stat-value" style="color:{{ $stats['total_kalori'] > $stats['target_kalori'] ? '#c62828' : '#1a6b3a' }};">
                    {{ number_format($stats['total_kalori']) }}
                </div>
                <div class="stat-sub">Target: {{ number_format($stats['target_kalori']) }} kkal</div>
            </div>
        </div>
    </div>

    {{-- Total Biaya --}}
    <div class="col-6 col-lg-3">
        <div class="stat-card">
            <div class="stat-icon" style="background:#e3f2fd;">💰</div>
            <div>
                <div class="stat-label">Biaya Produksi</div>
                <div class="stat-value" style="font-size:1.15rem;">
                    Rp{{ number_format($stats['total_biaya'], 0, ',', '.') }}
                </div>
                <div class="stat-sub">Budget: Rp{{ number_format($stats['budget_harian'], 0, ',', '.') }}</div>
            </div>
        </div>
    </div>

    {{-- Status Budget --}}
    <div class="col-6 col-lg-3">
        <div class="stat-card">
            <div class="stat-icon"
                 style="background:{{ $stats['status_budget'] === 'aman' ? '#e8f5ee' : ($stats['status_budget'] === 'warning' ? '#fff8e1' : '#fce4e4') }};">
                @if($stats['status_budget'] === 'aman') ✅
                @elseif($stats['status_budget'] === 'warning') ⚠️
                @else 🚨 @endif
            </div>
            <div>
                <div class="stat-label">Status Budget</div>
                <div class="stat-value" style="font-size:1.1rem; text-transform:capitalize;">
                    {{ ucfirst($stats['status_budget']) }}
                </div>
                <div class="stat-sub">
                    Sisa: Rp{{ number_format($stats['budget_harian'] - $stats['total_biaya'], 0, ',', '.') }}
                </div>
            </div>
        </div>
    </div>
</div>

{{-- ROW 2 : Progress Gizi + Chart Biaya --}}
<div class="row g-3 mb-4">

    {{-- Pemenuhan Gizi --}}
    <div class="col-lg-5">
        <div class="card card-mbg h-100">
            <div class="card-header d-flex justify-content-between align-items-center">
                <span><i class="fas fa-heart-pulse me-2" style="color:#1a6b3a;"></i> Pemenuhan Gizi Hari Ini</span>
                <span class="badge" style="background:#e8f5ee; color:#1a6b3a; font-size:.72rem;">AKG Harian</span>
            </div>
            <div class="card-body p-3">
                @php
                    $giziItems = [
                        ['label' => 'Energi (Kalori)', 'pct' => round($stats['total_kalori'] / $stats['target_kalori'] * 100), 'color' => '#f57c00', 'icon' => '🔥', 'val' => $stats['total_kalori'].' kkal'],
                        ['label' => 'Protein',         'pct' => $stats['persen_protein'],      'color' => '#1a6b3a', 'icon' => '🥩', 'val' => $stats['persen_protein'].'%'],
                        ['label' => 'Karbohidrat',     'pct' => $stats['persen_karbohidrat'],  'color' => '#2196f3', 'icon' => '🍚', 'val' => $stats['persen_karbohidrat'].'%'],
                        ['label' => 'Lemak',           'pct' => $stats['persen_lemak'],        'color' => '#9c27b0', 'icon' => '🧈', 'val' => $stats['persen_lemak'].'%'],
                    ];
                @endphp

                @foreach($giziItems as $g)
                <div class="mb-3">
                    <div class="d-flex justify-content-between mb-1">
                        <span style="font-size:.8rem; font-weight:600; color:#1a2e1d;">
                            {{ $g['icon'] }} {{ $g['label'] }}
                        </span>
                        <span style="font-size:.78rem; color:#7a9280;">{{ $g['val'] }}</span>
                    </div>
                    <div class="progress">
                        <div class="progress-bar" role="progressbar"
                             style="width:{{ min($g['pct'], 100) }}%; background:{{ $g['color'] }}; border-radius:6px;"
                             aria-valuenow="{{ $g['pct'] }}" aria-valuemin="0" aria-valuemax="100">
                        </div>
                    </div>
                    <div class="d-flex justify-content-between mt-1">
                        <span style="font-size:.68rem; color:#adb5bd;">0%</span>
                        <span style="font-size:.7rem; font-weight:600; color:{{ $g['color'] }};">{{ $g['pct'] }}%</span>
                        <span style="font-size:.68rem; color:#adb5bd;">100%</span>
                    </div>
                </div>
                @endforeach
            </div>
        </div>
    </div>

    {{-- Chart Biaya (Donut dummy) --}}
    <div class="col-lg-7">
        <div class="card card-mbg h-100">
            <div class="card-header">
                <i class="fas fa-chart-pie me-2" style="color:#1a6b3a;"></i> Distribusi Biaya Produksi
            </div>
            <div class="card-body d-flex align-items-center justify-content-center" style="min-height:230px;">
                <canvas id="chartBiaya" style="max-height:220px;"></canvas>
            </div>
        </div>
    </div>
</div>

{{-- ROW 3 : Budget Alert + Menu Hari Ini --}}
<div class="row g-3">

    {{-- Budget Alert --}}
    <div class="col-lg-4">
        <div class="card card-mbg">
            <div class="card-header d-flex justify-content-between">
                <span><i class="fas fa-bell me-2" style="color:#f57c00;"></i> Budget Alert</span>
                <span class="badge" style="background:#fce4e4; color:#c62828;">2 Peringatan</span>
            </div>
            <div class="card-body p-0">
                @php
                    $alerts = [
                        ['type'=>'warning', 'msg'=>'Biaya bahan baku mendekati batas anggaran (92%)', 'time'=>'08:45'],
                        ['type'=>'danger',  'msg'=>'Anggaran protein hewani melebihi batas hari ini', 'time'=>'09:12'],
                    ];
                @endphp
                @foreach($alerts as $alert)
                <div class="d-flex gap-3 p-3 border-bottom" style="border-color:#eef2ef !important;">
                    <div style="margin-top:.15rem;">
                        @if($alert['type'] === 'danger')
                            <span style="color:#c62828; font-size:1rem;">🚨</span>
                        @else
                            <span style="font-size:1rem;">⚠️</span>
                        @endif
                    </div>
                    <div class="flex-grow-1">
                        <div style="font-size:.8rem; color:#1a2e1d; font-weight:500;">{{ $alert['msg'] }}</div>
                        <div style="font-size:.7rem; color:#adb5bd; margin-top:.2rem;">Hari ini, {{ $alert['time'] }} WIB</div>
                    </div>
                </div>
                @endforeach
                <div class="p-3">
                    <a href="#" class="btn btn-sm w-100" style="background:#e8f5ee; color:#1a6b3a; border-radius:8px; font-size:.8rem; font-weight:600;">
                        Lihat Semua Alert
                    </a>
                </div>
            </div>
        </div>
    </div>

    {{-- Menu Hari Ini --}}
    <div class="col-lg-8">
        <div class="card card-mbg">
            <div class="card-header d-flex justify-content-between align-items-center">
                <span><i class="fas fa-utensils me-2" style="color:#1a6b3a;"></i> Menu Hari Ini</span>
                <a href="#" class="btn btn-sm" style="background:#e8f5ee; color:#1a6b3a; border-radius:8px; font-size:.75rem; font-weight:600;">
                    + Tambah Menu
                </a>
            </div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table mb-0" style="font-size:.82rem;">
                        <thead style="background:#f8fbf9;">
                            <tr>
                                <th style="padding:.75rem 1rem; color:#7a9280; font-weight:600; font-size:.75rem; border:none;">Sesi</th>
                                <th style="padding:.75rem 1rem; color:#7a9280; font-weight:600; font-size:.75rem; border:none;">Nama Menu</th>
                                <th style="padding:.75rem 1rem; color:#7a9280; font-weight:600; font-size:.75rem; border:none;">Kalori</th>
                                <th style="padding:.75rem 1rem; color:#7a9280; font-weight:600; font-size:.75rem; border:none;">Biaya/Porsi</th>
                                <th style="padding:.75rem 1rem; color:#7a9280; font-weight:600; font-size:.75rem; border:none;">Status Gizi</th>
                            </tr>
                        </thead>
                        <tbody>
                            @php
                                $menus = [
                                    ['sesi'=>'Pagi',  'nama'=>'Nasi Putih + Ayam Goreng + Sayur Bayam', 'kalori'=>'720 kkal', 'biaya'=>'Rp 9.500',  'status'=>'terpenuhi'],
                                    ['sesi'=>'Siang', 'nama'=>'Nasi Putih + Ikan Bakar + Tempe + Lalapan', 'kalori'=>'850 kkal', 'biaya'=>'Rp 11.200', 'status'=>'terpenuhi'],
                                    ['sesi'=>'Sore',  'nama'=>'Nasi Putih + Telur Dadar + Tumis Kangkung', 'kalori'=>'580 kkal', 'biaya'=>'Rp 7.800',  'status'=>'kurang'],
                                ];
                            @endphp
                            @foreach($menus as $m)
                            <tr>
                                <td style="padding:.75rem 1rem; border-color:#f0f5f1; vertical-align:middle;">
                                    <span class="badge" style="background:#e8f5ee; color:#1a6b3a; font-size:.72rem;">{{ $m['sesi'] }}</span>
                                </td>
                                <td style="padding:.75rem 1rem; border-color:#f0f5f1; vertical-align:middle; font-weight:500; color:#1a2e1d;">
                                    {{ $m['nama'] }}
                                </td>
                                <td style="padding:.75rem 1rem; border-color:#f0f5f1; vertical-align:middle; color:#f57c00; font-weight:600;">
                                    {{ $m['kalori'] }}
                                </td>
                                <td style="padding:.75rem 1rem; border-color:#f0f5f1; vertical-align:middle; font-weight:600; color:#2196f3;">
                                    {{ $m['biaya'] }}
                                </td>
                                <td style="padding:.75rem 1rem; border-color:#f0f5f1; vertical-align:middle;">
                                    @if($m['status'] === 'terpenuhi')
                                        <span class="badge" style="background:#e8f5ee; color:#1a6b3a; font-size:.72rem;">✓ Terpenuhi</span>
                                    @else
                                        <span class="badge" style="background:#fff8e1; color:#f57c00; font-size:.72rem;">⚠ Kurang</span>
                                    @endif
                                </td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

</div>

@endsection

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.3/dist/chart.umd.min.js"></script>
<script>
    const ctx = document.getElementById('chartBiaya').getContext('2d');
    new Chart(ctx, {
        type: 'doughnut',
        data: {
            labels: ['Bahan Baku', 'Tenaga Kerja', 'Overhead'],
            datasets: [{
                data: [185000, 65000, 35000],
                backgroundColor: ['#2d9e5f', '#f57c00', '#2196f3'],
                borderWidth: 0,
                hoverOffset: 8,
            }]
        },
        options: {
            cutout: '65%',
            plugins: {
                legend: {
                    position: 'right',
                    labels: {
                        usePointStyle: true,
                        pointStyle: 'circle',
                        font: { size: 12, family: 'Plus Jakarta Sans' },
                        color: '#1a2e1d',
                        padding: 16,
                        generateLabels: (chart) => {
                            const data = chart.data;
                            return data.labels.map((label, i) => ({
                                text: `${label}\nRp ${data.datasets[0].data[i].toLocaleString('id-ID')}`,
                                fillStyle: data.datasets[0].backgroundColor[i],
                                strokeStyle: 'transparent',
                                pointStyle: 'circle',
                            }));
                        }
                    }
                },
                tooltip: {
                    callbacks: {
                        label: (ctx) => ` Rp ${ctx.raw.toLocaleString('id-ID')}`
                    }
                }
            }
        }
    });
</script>
@endpush