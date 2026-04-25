<nav id="sidebar" style="display:flex; flex-direction:column; height:100vh; overflow:hidden;">
    {{-- Brand --}}
    <div class="sidebar-brand" style="flex-shrink:0;">
        <div style="font-size:1.8rem; margin-bottom:.3rem;">🍱</div>
        <h5>Dashboard MBG</h5>
        <small>Monitoring Gizi & Biaya Produksi</small>
    </div>

    {{-- Unit info --}}
    <div style="flex-shrink:0; padding:.75rem 1.25rem; background:rgba(255,255,255,.07); margin:.75rem; border-radius:10px;">
        <div style="font-size:.68rem; color:rgba(255,255,255,.45); font-weight:600; text-transform:uppercase; letter-spacing:.06em;">Unit SPPG</div>
        <div style="font-size:.82rem; color:#fff; font-weight:600; margin-top:.2rem;">
            {{ Auth::user()->unit_sppg ?? '—' }}
        </div>
    </div>

    {{-- Navigation — area ini yang scroll --}}
    <nav class="flex-grow-1 mt-1" style="overflow-y:auto; overflow-x:hidden; min-height:0;
        scrollbar-width:thin;
        scrollbar-color:rgba(255,255,255,.2) transparent;">

        <div class="nav-section">Menu Utama</div>

        <a href="{{ route('dashboard') }}"
           class="nav-link {{ request()->routeIs('dashboard') ? 'active' : '' }}">
            <i class="fas fa-gauge-high"></i> Dashboard
        </a>

        <div class="nav-section">Manajemen Menu</div>

        <a href="{{ route('menu-harian.index') }}"
            class="nav-link {{ request()->routeIs('menu-harian.*') ? 'active' : '' }}">
            <i class="fas fa-utensils"></i> Menu Harian
        </a>
        <a href="{{ route('bahan-pangan.index') }}"
            class="nav-link {{ request()->routeIs('bahan-pangan.*') ? 'active' : '' }}">
            <i class="fas fa-carrot"></i> Bahan Pangan (TKPI)
        </a>
        <a href="{{ route('simulasi.index') }}"
            class="nav-link {{ request()->routeIs('simulasi.*') ? 'active' : '' }}">
            <i class="fas fa-flask"></i> Simulasi Menu
        </a>

        <div class="nav-section">Monitoring</div>

        <a href="{{ route('gizi.dashboard') }}"
            class="nav-link {{ request()->routeIs('gizi.*') ? 'active' : '' }}">
            <i class="fas fa-heart-pulse"></i> Monitor Gizi
        </a>
        <a href="{{ route('biaya.dashboard') }}"
            class="nav-link {{ request()->routeIs('biaya.*') ? 'active' : '' }}">
            <i class="fas fa-coins"></i> Biaya Produksi
        </a>
        <a href="{{ route('budget-alert.index') }}"
            class="nav-link {{ request()->routeIs('budget-alert.*') ? 'active' : '' }}">
            <i class="fas fa-bell"></i> Budget Alert
            @if(isset($navAlertCount) && $navAlertCount > 0)
            <span class="badge ms-auto" style="background:rgba(255,100,100,.85); font-size:.65rem;">
                {{ $navAlertCount }}
            </span>
            @endif
        </a>
        @if(Auth::user()->role === 'admin')
            <div class="nav-section">Administrasi</div>
            <a class="nav-link {{ request()->routeIs('anggaran.*') ? 'active' : '' }}"
                href="{{ route('anggaran.index') }}">
                <i class="fas fa-wallet"></i> Kelola Anggaran
            </a>
            <a href="{{ route('users.index') }}"
                class="nav-link {{ request()->routeIs('users.*') ? 'active' : '' }}">
                <i class="fas fa-users"></i> Kelola Pengguna
            </a>
            <a href="#" class="nav-link">
                <i class="fas fa-building"></i> Kelola SPPG
            </a>
            <a href="{{ route('import-tkpi.index') }}"
                class="nav-link {{ request()->routeIs('import-tkpi.*') ? 'active' : '' }}">
                <i class="fas fa-file-import"></i> Import TKPI
            </a>
        @endif

        <div class="nav-section">Laporan</div>
        <a href="{{ route('laporan.index', ['jenis' => 'gizi']) }}"
            class="nav-link {{ request()->routeIs('laporan.*') && request('jenis','gizi') === 'gizi' ? 'active' : '' }}">
            <i class="fas fa-chart-bar"></i> Laporan Gizi
        </a>
        <a href="{{ route('laporan.index', ['jenis' => 'biaya']) }}"
            class="nav-link {{ request()->routeIs('laporan.*') && request('jenis') === 'biaya' ? 'active' : '' }}">
            <i class="fas fa-file-invoice-dollar"></i> Laporan Biaya
        </a>

        {{-- Spacer bawah supaya item terakhir tidak nempel logout --}}
        <div style="height:.75rem;"></div>

    </nav>

    {{-- Logout — selalu terlihat di bawah, tidak ikut scroll --}}
    <div style="flex-shrink:0; padding:.75rem; border-top:1px solid rgba(255,255,255,.1);">
        <form action="{{ route('logout') }}" method="POST">
            @csrf
            <button type="submit" class="nav-link w-100 border-0 bg-transparent text-start"
                    style="color:rgba(255,255,255,.6);">
                <i class="fas fa-right-from-bracket"></i> Keluar
            </button>
        </form>
    </div>
</nav>