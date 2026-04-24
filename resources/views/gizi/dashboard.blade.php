@extends('layouts.app')

@section('title', 'Dashboard Monitoring Gizi')

@push('styles')
<style>
.gizi-card {
    border-left: 4px solid #1a6b3a;
    transition: transform .2s;
}
.gizi-card:hover { transform: translateY(-2px); }

.progress-gizi { height: 12px; border-radius: 6px; }
.progress-bar-kurang  { background: #dc3545; }
.progress-bar-cukup   { background: #1a6b3a; }
.progress-bar-lebih   { background: #ffc107; }

.badge-status-kurang  { background: #fff3cd; color: #856404; border: 1px solid #ffc107; }
.badge-status-cukup   { background: #d1e7dd; color: #0a3622; border: 1px solid #1a6b3a; }
.badge-status-lebih   { background: #f8d7da; color: #842029; border: 1px solid #dc3545; }

.chart-container { position: relative; height: 300px; }
.nutrisi-icon { width: 36px; height: 36px; border-radius: 8px;
    display: flex; align-items: center; justify-content: center;
    background: rgba(26,107,58,.1); color: #1a6b3a; font-size: .9rem; }
</style>
@endpush

@section('content')
@php
    use App\Constants\AKG;
    $keys = ['energi','protein','lemak','karbohidrat','serat','kalsium','besi','vit_c'];
@endphp

<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <h4 class="mb-0 fw-bold" style="color:#1a6b3a">
            <i class="fas fa-chart-line me-2"></i>Dashboard Monitoring Gizi
        </h4>
        <small class="text-muted">Unit SPPG: {{ Auth::user()->unit_sppg }}</small>
    </div>
    {{-- Filter Bulan --}}
    <form method="GET" class="d-flex gap-2 align-items-center">
        <input type="month" name="bulan" value="{{ $bulan }}"
               class="form-control form-control-sm" style="width:160px"
               onchange="this.form.submit()">
        <a href="{{ route('menu-harian.create') }}" class="btn btn-sm btn-success">
            <i class="fas fa-plus me-1"></i>Input Menu
        </a>
    </form>
</div>

{{-- ═══ STATUS HARI INI ════════════════════════════════════════════ --}}
<div class="card shadow-sm mb-4">
    <div class="card-header py-2 fw-semibold" style="background:#1a6b3a;color:#fff">
        <i class="fas fa-calendar-day me-2"></i>Status Gizi Hari Ini
        — {{ now()->translatedFormat('d F Y') }}
    </div>
    <div class="card-body">
        @if($menuHariIni && $giziHariIni)
            <div class="row g-3">
                @foreach(['energi','protein','lemak','karbohidrat'] as $k)
                @php
                    $val   = $giziHariIni[$k] ?? 0;
                    $acuan = AKG::MAKAN_SIANG[$k];
                    $pct   = $acuan > 0 ? min(round($val/$acuan*100), 200) : 0;
                    $cls   = $pct < 70 ? 'kurang' : ($pct > 130 ? 'lebih' : 'cukup');
                    $info  = AKG::LABEL[$k];
                @endphp
                <div class="col-sm-6 col-md-3">
                    <div class="card gizi-card h-100">
                        <div class="card-body p-3">
                            <div class="d-flex align-items-center gap-2 mb-2">
                                <div class="nutrisi-icon"><i class="fas {{ $info['icon'] }}"></i></div>
                                <span class="fw-semibold small">{{ $info['label'] }}</span>
                            </div>
                            <div class="fs-4 fw-bold mb-1">
                                {{ number_format($val,1) }}
                                <small class="fs-6 text-muted">{{ $info['satuan'] }}</small>
                            </div>
                            <div class="progress progress-gizi mb-1">
                                <div class="progress-bar progress-bar-{{ $cls }}"
                                     style="width:{{ min($pct,100) }}%"></div>
                            </div>
                            <div class="d-flex justify-content-between align-items-center">
                                <small class="text-muted">Target: {{ $acuan }} {{ $info['satuan'] }}</small>
                                <span class="badge badge-status-{{ $cls }}">{{ $pct }}%</span>
                            </div>
                        </div>
                    </div>
                </div>
                @endforeach
            </div>

            @if($menuHariIni->status === 'draft')
            <div class="alert alert-warning mt-3 mb-0 py-2">
                <i class="fas fa-exclamation-triangle me-1"></i>
                Menu hari ini masih berstatus <strong>Draft</strong>. Finalisasi untuk menghitung gizi resmi.
                <a href="{{ route('menu-harian.show', $menuHariIni) }}" class="alert-link ms-2">Lihat Menu →</a>
            </div>
            @endif
        @else
            <div class="text-center text-muted py-4">
                <i class="fas fa-utensils fa-2x mb-2 d-block opacity-25"></i>
                Belum ada menu yang diinput hari ini.
                <div class="mt-2">
                    <a href="{{ route('menu-harian.create') }}" class="btn btn-sm btn-success">
                        <i class="fas fa-plus me-1"></i>Input Menu Sekarang
                    </a>
                </div>
            </div>
        @endif
    </div>
</div>

{{-- ═══ RINGKASAN RATA-RATA BULAN INI ══════════════════════════════ --}}
<div class="row g-3 mb-4">
    <div class="col-md-8">
        <div class="card shadow-sm h-100">
            <div class="card-header py-2 fw-semibold border-bottom">
                <i class="fas fa-chart-bar me-2 text-success"></i>
                Rata-rata Gizi vs. AKG Makan Siang
                <span class="badge bg-secondary ms-2">{{ $jumlahHari }} hari</span>
            </div>
            <div class="card-body">
                <div class="chart-container">
                    <canvas id="chartAkg"></canvas>
                </div>
            </div>
        </div>
    </div>

    <div class="col-md-4">
        <div class="card shadow-sm h-100">
            <div class="card-header py-2 fw-semibold border-bottom">
                <i class="fas fa-list-check me-2 text-success"></i>Detail Rata-rata
            </div>
            <div class="card-body p-0">
                <div class="list-group list-group-flush">
                    @foreach($keys as $k)
                    @php
                        $val   = $rataGizi[$k] ?? 0;
                        $pct   = $persenAkg[$k] ?? 0;
                        $acuan = AKG::MAKAN_SIANG[$k];
                        $cls   = $pct < 70 ? 'kurang' : ($pct > 130 ? 'lebih' : 'cukup');
                        $info  = AKG::LABEL[$k];
                    @endphp
                    <div class="list-group-item px-3 py-2">
                        <div class="d-flex justify-content-between align-items-center mb-1">
                            <span class="small fw-semibold">
                                <i class="fas {{ $info['icon'] }} me-1 text-success opacity-75"></i>
                                {{ $info['label'] }}
                            </span>
                            <span class="badge badge-status-{{ $cls }}">{{ $pct }}%</span>
                        </div>
                        <div class="progress progress-gizi mb-1">
                            <div class="progress-bar progress-bar-{{ $cls }}"
                                 style="width:{{ min($pct,100) }}%"></div>
                        </div>
                        <small class="text-muted">
                            {{ number_format($val,1) }} / {{ $acuan }} {{ $info['satuan'] }}
                        </small>
                    </div>
                    @endforeach
                </div>
            </div>
        </div>
    </div>
</div>

{{-- ═══ TREN HARIAN ════════════════════════════════════════════════ --}}
@if(count($trendData) > 0)
<div class="card shadow-sm mb-4">
    <div class="card-header py-2 fw-semibold border-bottom">
        <i class="fas fa-chart-line me-2 text-success"></i>Tren Energi Harian
        <span class="text-muted fw-normal small ms-2">
            ({{ \Carbon\Carbon::createFromFormat('Y-m',$bulan)->translatedFormat('F Y') }})
        </span>
    </div>
    <div class="card-body">
        <div class="chart-container">
            <canvas id="chartTrend"></canvas>
        </div>
    </div>
</div>
@endif

{{-- ═══ TABEL RINGKASAN MENU ════════════════════════════════════════ --}}
<div class="card shadow-sm">
    <div class="card-header py-2 fw-semibold border-bottom">
        <i class="fas fa-table me-2 text-success"></i>Rekap Menu Final Bulan Ini
    </div>
    @if($menus->count())
    <div class="table-responsive">
        <table class="table table-hover table-sm align-middle mb-0">
            <thead class="table-light">
                <tr>
                    <th>Tanggal</th>
                    <th>Menu</th>
                    <th class="text-end">Energi (kkal)</th>
                    <th class="text-end">Protein (g)</th>
                    <th class="text-end">Lemak (g)</th>
                    <th class="text-end">Karbo (g)</th>
                    <th class="text-center">Status Gizi</th>
                    <th></th>
                </tr>
            </thead>
            <tbody>
                @foreach($menus as $menu)
                @php
                    $g   = $menu->totalGizi();
                    $pct = AKG::MAKAN_SIANG['energi'] > 0
                        ? round($g['energi']/AKG::MAKAN_SIANG['energi']*100) : 0;
                    $cls = $pct < 70 ? 'kurang' : ($pct > 130 ? 'lebih' : 'cukup');
                @endphp
                <tr>
                    <td class="text-nowrap">
                        {{ $menu->tanggal->translatedFormat('d M Y') }}
                    </td>
                    <td>{{ $menu->nama_menu ?: '—' }}</td>
                    <td class="text-end">{{ number_format($g['energi'],1) }}</td>
                    <td class="text-end">{{ number_format($g['protein'],1) }}</td>
                    <td class="text-end">{{ number_format($g['lemak'],1) }}</td>
                    <td class="text-end">{{ number_format($g['karbohidrat'],1) }}</td>
                    <td class="text-center">
                        <span class="badge badge-status-{{ $cls }}">
                            {{ ucfirst($cls) }} ({{ $pct }}%)
                        </span>
                    </td>
                    <td>
                        <a href="{{ route('menu-harian.show', $menu) }}"
                           class="btn btn-xs btn-outline-secondary btn-sm py-0">
                            <i class="fas fa-eye"></i>
                        </a>
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>
    @else
    <div class="card-body text-center text-muted py-5">
        <i class="fas fa-chart-line fa-2x mb-2 d-block opacity-25"></i>
        Belum ada menu final di bulan ini.
    </div>
    @endif
</div>
@endsection

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.min.js"></script>

<script>
// ─── Data dari PHP ───────────────────────────────────────────────
const rataGizi  = @json($rataGizi);
const persenAkg = @json($persenAkg);
const trendData = @json($trendData);
const akgSiang  = @json(\App\Constants\AKG::MAKAN_SIANG);

const GREEN  = '#1a6b3a';
const YELLOW = '#ffc107';
const RED    = '#dc3545';
const GRAY   = 'rgba(26,107,58,.15)';

// ─── Helper: warna berdasarkan persen ────────────────────────────
function warnaPct(pct) {
    return pct < 70 ? RED : pct > 130 ? YELLOW : GREEN;
}

// ─── Chart 1: Bar chart rata-rata vs AKG ─────────────────────────
const labelKeys = ['energi','protein','lemak','karbohidrat','serat','kalsium','besi','vit_c'];
const labelMap  = {
    energi:'Energi',protein:'Protein',lemak:'Lemak',
    karbohidrat:'Karbo',serat:'Serat',kalsium:'Kalsium',besi:'Fe',vit_c:'Vit C'
};

new Chart(document.getElementById('chartAkg'), {
    type: 'bar',
    data: {
        labels: labelKeys.map(k => labelMap[k]),
        datasets: [
            {
                label: 'Rata-rata Aktual',
                data: labelKeys.map(k => rataGizi[k] || 0),
                backgroundColor: labelKeys.map(k => warnaPct(persenAkg[k] || 0)),
                borderRadius: 4,
            },
            {
                label: 'Target AKG Siang',
                data: labelKeys.map(k => akgSiang[k] || 0),
                backgroundColor: GRAY,
                borderColor: GREEN,
                borderWidth: 1.5,
                borderRadius: 4,
                type: 'bar',
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
                    label: ctx => ` ${ctx.dataset.label}: ${ctx.parsed.y.toFixed(1)}`
                }
            }
        },
        scales: {
            y: { beginAtZero: true, grid: { color: 'rgba(0,0,0,.05)' } },
            x: { grid: { display: false } }
        }
    }
});

// ─── Chart 2: Line chart tren energi harian ──────────────────────
@if(count($trendData) > 1)
new Chart(document.getElementById('chartTrend'), {
    type: 'line',
    data: {
        labels: trendData.map(d => d.tanggal),
        datasets: [
            {
                label: 'Energi (kkal)',
                data: trendData.map(d => d.energi),
                borderColor: GREEN,
                backgroundColor: 'rgba(26,107,58,.1)',
                fill: true,
                tension: 0.3,
                pointBackgroundColor: GREEN,
                pointRadius: 4,
            },
            {
                label: 'Target AKG',
                data: trendData.map(() => akgSiang.energi),
                borderColor: RED,
                borderDash: [6,3],
                borderWidth: 1.5,
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
                    label: ctx => ` ${ctx.dataset.label}: ${ctx.parsed.y.toFixed(1)} kkal`
                }
            }
        },
        scales: {
            y: { beginAtZero: false, grid: { color: 'rgba(0,0,0,.05)' } },
            x: { grid: { display: false } }
        }
    }
});
@endif
</script>
@endpush