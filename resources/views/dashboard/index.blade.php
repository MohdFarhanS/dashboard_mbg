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

    {{-- Chart Biaya per Kategori --}}
    <div class="col-lg-7">
        <div class="card card-mbg h-100">
            <div class="card-header">
                <i class="fas fa-chart-pie me-2" style="color:#1a6b3a;"></i> Distribusi Biaya per Kategori Bahan
            </div>
            <div class="card-body d-flex align-items-center justify-content-center" style="min-height:230px;">
                @if(!empty($stats['distribusi_biaya']))
                    <canvas id="chartBiaya" style="max-height:220px;"></canvas>
                @else
                    <div class="text-center text-muted">
                        <i class="fas fa-chart-pie fa-2x mb-2 d-block opacity-25"></i>
                        <div style="font-size:.85rem;">Belum ada data harga bahan untuk hari ini.</div>
                        <small>Pastikan harga bahan sudah diinput di menu <strong>Biaya Produksi → Kelola Harga</strong></small>
                    </div>
                @endif
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
                @if($stats['total_alert'] > 0)
                    <span class="badge" style="background:#fce4e4; color:#c62828;">
                        {{ $stats['total_alert'] }} Peringatan
                    </span>
                @else
                    <span class="badge" style="background:#d1e7dd; color:#0a3622;">Aman</span>
                @endif
            </div>
            <div class="card-body p-0">
                @forelse($stats['alert_list'] as $alert)
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
                        <div style="font-size:.7rem; color:#adb5bd; margin-top:.2rem;">{{ $alert['time'] }}</div>
                    </div>
                </div>
                @empty
                <div class="text-center text-muted py-4" style="font-size:.85rem;">
                    <i class="fas fa-check-circle text-success d-block mb-2 fs-4"></i>
                    Semua menu dalam batas anggaran
                </div>
                @endforelse
                <div class="p-3">
                    <a href="{{ route('biaya.dashboard') }}" class="btn btn-sm w-100"
                    style="background:#e8f5ee; color:#1a6b3a; border-radius:8px; font-size:.8rem; font-weight:600;">
                        Lihat Dashboard Biaya →
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
                @if(auth()->user()->role === 'pengelola')
                <a href="{{ route('menu-harian.create') }}" class="btn btn-sm"
                style="background:#e8f5ee; color:#1a6b3a; border-radius:8px; font-size:.75rem; font-weight:600;">
                    + Tambah Menu
                </a>
                @endif
            </div>
            <div class="card-body p-0">
                @if($menusHariIni->count())
                <div class="table-responsive">
                    <table class="table mb-0" style="font-size:.82rem;">
                        <thead style="background:#f8fbf9;">
                            <tr>
                                <th style="padding:.75rem 1rem; color:#7a9280; font-weight:600; font-size:.75rem; border:none;">Nama Menu</th>
                                <th style="padding:.75rem 1rem; color:#7a9280; font-weight:600; font-size:.75rem; border:none;">Kalori</th>
                                <th style="padding:.75rem 1rem; color:#7a9280; font-weight:600; font-size:.75rem; border:none;">Biaya/Porsi</th>
                                <th style="padding:.75rem 1rem; color:#7a9280; font-weight:600; font-size:.75rem; border:none;">Status</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($menusHariIni as $menu)
                            @php
                                $g      = $menu->totalGizi();
                                $b      = $menu->totalBiaya();
                                $status = $menu->statusAnggaran();
                            @endphp
                            <tr>
                                <td style="padding:.75rem 1rem; border-color:#f0f5f1; vertical-align:middle; font-weight:500; color:#1a2e1d;">
                                    <a href="{{ route('menu-harian.show', $menu) }}"
                                    class="text-decoration-none text-dark">
                                        {{ $menu->nama_menu ?? '(tanpa nama)' }}
                                    </a>
                                    <div style="font-size:.7rem; color:#adb5bd;">{{ $menu->status === 'final' ? '🔒 Final' : '✏️ Draft' }}</div>
                                </td>
                                <td style="padding:.75rem 1rem; border-color:#f0f5f1; vertical-align:middle; color:#f57c00; font-weight:600;">
                                    {{ number_format($g['energi'], 0) }} kkal
                                </td>
                                <td style="padding:.75rem 1rem; border-color:#f0f5f1; vertical-align:middle; font-weight:600; color:#2196f3;">
                                    Rp {{ number_format($b['cost_per_porsi'], 0, ',', '.') }}
                                </td>
                                <td style="padding:.75rem 1rem; border-color:#f0f5f1; vertical-align:middle;">
                                    @if($status === 'over')
                                        <span class="badge" style="background:#fce4e4; color:#c62828; font-size:.72rem;">🚨 Over</span>
                                    @elseif($status === 'warning')
                                        <span class="badge" style="background:#fff8e1; color:#f57c00; font-size:.72rem;">⚠ Mendekati</span>
                                    @elseif($status === 'aman')
                                        <span class="badge" style="background:#e8f5ee; color:#1a6b3a; font-size:.72rem;">✓ Aman</span>
                                    @else
                                        <span class="badge" style="background:#f0f0f0; color:#aaa; font-size:.72rem;">— Draft</span>
                                    @endif
                                </td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
                @else
                <div class="text-center text-muted py-5">
                    <i class="fas fa-utensils fa-2x mb-2 d-block opacity-25"></i>
                    <div style="font-size:.85rem;">Belum ada menu yang diinput hari ini.</div>
                    @if(auth()->user()->role === 'pengelola')
                    <a href="{{ route('menu-harian.create') }}" class="btn btn-sm btn-success mt-2"
                    style="background:#1a6b3a; border-color:#1a6b3a;">
                        <i class="fas fa-plus me-1"></i>Input Menu Sekarang
                    </a>
                    @endif
                </div>
                @endif
            </div>
        </div>
    </div>

</div>

@endsection

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.3/dist/chart.umd.min.js"></script>
@if(!empty($stats['distribusi_biaya']))
<script>
const distribusi = @json($stats['distribusi_biaya']);

const WARNA_KATEGORI = {
    'Serealia'  : '#2d9e5f',
    'Daging'    : '#e53935',
    'Ikan'      : '#1e88e5',
    'Telur'     : '#fdd835',
    'Sayuran'   : '#43a047',
    'Buah'      : '#fb8c00',
    'Kacang'    : '#8d6e63',
    'Umbi'      : '#f06292',
    'Susu'      : '#90caf9',
    'Lemak'     : '#ffb74d',
    'Bumbu'     : '#ce93d8',
    'Gula'      : '#ef9a9a',
    'Minuman'   : '#80deea',
    'Lainnya'   : '#b0bec5',
};

const labels = Object.keys(distribusi);
const values = Object.values(distribusi);
const colors = labels.map(l => WARNA_KATEGORI[l] || '#b0bec5');

const ctx = document.getElementById('chartBiaya').getContext('2d');
new Chart(ctx, {
    type: 'doughnut',
    data: {
        labels,
        datasets: [{
            data: values,
            backgroundColor: colors,
            borderWidth: 0,
            hoverOffset: 8,
        }]
    },
    options: {
        cutout: '60%',
        plugins: {
            legend: {
                position: 'right',
                labels: {
                    usePointStyle: true,
                    pointStyle: 'circle',
                    font: { size: 11, family: 'Plus Jakarta Sans' },
                    color: '#1a2e1d',
                    padding: 12,
                    generateLabels: (chart) => {
                        const d = chart.data;
                        const total = d.datasets[0].data.reduce((a, b) => a + b, 0);
                        return d.labels.map((label, i) => ({
                            text: `${label}  Rp ${Math.round(d.datasets[0].data[i]).toLocaleString('id-ID')}`,
                            fillStyle: d.datasets[0].backgroundColor[i],
                            strokeStyle: 'transparent',
                            pointStyle: 'circle',
                        }));
                    }
                }
            },
            tooltip: {
                callbacks: {
                    label: (ctx) => {
                        const total = ctx.dataset.data.reduce((a, b) => a + b, 0);
                        const pct   = total > 0 ? ((ctx.raw / total) * 100).toFixed(1) : 0;
                        return ` Rp ${Math.round(ctx.raw).toLocaleString('id-ID')} (${pct}%)`;
                    }
                }
            }
        }
    }
});
</script>
@endif
@endpush