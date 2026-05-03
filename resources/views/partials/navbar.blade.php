<div id="topnav">
    {{-- Toggle mobile --}}
    <button onclick="toggleSidebar()" class="btn btn-sm d-md-none"
            style="color:#0f4c81; background:var(--primary-pale); border:none; border-radius:8px; padding:.4rem .65rem;">
        <i class="fas fa-bars"></i>
    </button>

    {{-- Page title (diisi tiap halaman) --}}
    <span class="page-title">@yield('page-title', 'Dashboard')</span>

    {{-- Tanggal --}}
    <span class="d-none d-md-flex align-items-center"
          style="font-size:.78rem; color:#6b8ba4; background:var(--primary-pale);
                 padding:.3rem .85rem; border-radius:20px; gap:.4rem; margin-left:.5rem;">
        <i class="fas fa-calendar-day" style="color:var(--primary);"></i>
        <span id="tanggal-hari-ini"></span>
    </span>

    <div class="topnav-right">
        {{-- Notifikasi --}}
    <div class="dropdown">
        <button class="btn btn-sm position-relative dropdown-toggle"
                data-bs-toggle="dropdown"
                data-bs-auto-close="outside"
                style="background:var(--primary-pale); border:none; border-radius:8px;
                    color:var(--primary); width:36px; height:36px; padding:0;">
            <i class="fas fa-bell"></i>
            @if(isset($navAlertCount) && $navAlertCount > 0)
            <span class="position-absolute top-0 start-100 translate-middle badge rounded-pill bg-danger"
                style="font-size:.55rem; pointer-events:none;">
                {{ $navAlertCount }}
            </span>
            @endif
        </button>

        <ul class="dropdown-menu dropdown-menu-end shadow-sm p-0"
            style="border:none; border-radius:12px; min-width:300px; overflow:hidden;">

            {{-- Header --}}
            <li class="px-3 py-2 d-flex justify-content-between align-items-center"
                style="background:var(--primary-pale); border-bottom:1px solid #dee2e6;">
                <span style="font-size:.82rem; font-weight:700; color:var(--primary)">
                    <i class="fas fa-bell me-1"></i> Notifikasi
                </span>
                @if(isset($navAlertCount) && $navAlertCount > 0)
                <span class="badge bg-danger" style="font-size:.65rem;">
                    {{ $navAlertCount }} peringatan
                </span>
                @endif
            </li>

            {{-- Isi notifikasi --}}
            @if(isset($navAlerts) && count($navAlerts) > 0)
                @foreach($navAlerts as $alert)
                <li>
                    <a href="{{ route('budget-alert.index') }}"
                    class="dropdown-item py-2 px-3"
                    style="border-bottom:1px solid #f5f5f5; white-space:normal;">
                        <div class="d-flex gap-2 align-items-start">
                            <span style="font-size:1rem; flex-shrink:0;">
                                {{ $alert['type'] === 'danger' ? '🚨' : '⚠️' }}
                            </span>
                            <div>
                                <div style="font-size:.8rem; font-weight:500; color:#1a2e1d;">
                                    {{ $alert['msg'] }}
                                </div>
                                <div style="font-size:.7rem; color:#adb5bd;">
                                    {{ $alert['time'] }}
                                </div>
                            </div>
                        </div>
                    </a>
                </li>
                @endforeach
            @else
                <li class="text-center py-4 text-muted">
                    <i class="fas fa-check-circle text-primary d-block mb-1" style="font-size:1.3rem;"></i>
                    <span style="font-size:.8rem;">Semua menu dalam batas anggaran</span>
                </li>
            @endif

            {{-- Footer --}}
            <li style="border-top:1px solid #f0f0f0;">
                <a href="{{ route('budget-alert.index') }}"
                class="dropdown-item text-center py-2"
                style="font-size:.8rem; font-weight:600; color:var(--primary);">
                    Lihat Budget Alert →
                </a>
            </li>
        </ul>
    </div>
    </div>
</div>

<script>
    // Tampilkan tanggal hari ini dalam Bahasa Indonesia
    const el = document.getElementById('tanggal-hari-ini');
    if (el) {
        el.textContent = new Date().toLocaleDateString('id-ID', {
            weekday: 'long', day: 'numeric', month: 'long', year: 'numeric'
        });
    }
</script>