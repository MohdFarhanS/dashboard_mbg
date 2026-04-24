<div id="topnav">
    {{-- Toggle mobile --}}
    <button onclick="toggleSidebar()" class="btn btn-sm d-md-none"
            style="color:#1a6b3a; background:var(--primary-pale); border:none; border-radius:8px; padding:.4rem .65rem;">
        <i class="fas fa-bars"></i>
    </button>

    {{-- Page title (diisi tiap halaman) --}}
    <span class="page-title">@yield('page-title', 'Dashboard')</span>

    {{-- Tanggal --}}
    <span class="d-none d-md-flex align-items-center"
          style="font-size:.78rem; color:#7a9280; background:var(--primary-pale);
                 padding:.3rem .85rem; border-radius:20px; gap:.4rem; margin-left:.5rem;">
        <i class="fas fa-calendar-day" style="color:var(--primary);"></i>
        <span id="tanggal-hari-ini"></span>
    </span>

    <div class="topnav-right">
        {{-- Notifikasi --}}
        <button class="btn btn-sm position-relative"
                style="background:var(--primary-pale); border:none; border-radius:8px;
                       color:var(--primary); width:36px; height:36px; padding:0;">
            <i class="fas fa-bell"></i>
            <span class="position-absolute top-0 start-100 translate-middle badge rounded-pill bg-danger"
                  style="font-size:.55rem;">2</span>
        </button>

        {{-- User dropdown --}}
        <div class="dropdown">
            <button class="btn btn-sm dropdown-toggle d-flex align-items-center gap-2"
                    style="background:var(--primary-pale); border:none; border-radius:8px; padding:.4rem .85rem;"
                    data-bs-toggle="dropdown">
                <div class="avatar">{{ strtoupper(substr(Auth::user()->name, 0, 1)) }}</div>
                <div class="text-start d-none d-md-block">
                    <div style="font-size:.78rem; font-weight:600; color:#1a2e1d; line-height:1.2;">
                        {{ Auth::user()->name }}
                    </div>
                    <div style="font-size:.68rem; color:#7a9280; text-transform:capitalize;">
                        {{ Auth::user()->role }}
                    </div>
                </div>
            </button>
            <ul class="dropdown-menu dropdown-menu-end shadow-sm" style="border:none; border-radius:10px; min-width:180px;">
                <li><a class="dropdown-item" href="#"><i class="fas fa-user-circle me-2 text-muted"></i> Profil Saya</a></li>
                <li><a class="dropdown-item" href="#"><i class="fas fa-gear me-2 text-muted"></i> Pengaturan</a></li>
                <li><hr class="dropdown-divider"></li>
                <li>
                    <form action="{{ route('logout') }}" method="POST">
                        @csrf
                        <button class="dropdown-item text-danger">
                            <i class="fas fa-right-from-bracket me-2"></i> Keluar
                        </button>
                    </form>
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