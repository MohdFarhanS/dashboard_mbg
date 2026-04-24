<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title', 'Dashboard MBG')</title>

    {{-- Bootstrap 5 --}}
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    {{-- Font Awesome --}}
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css" rel="stylesheet">
    {{-- Google Fonts --}}
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700&display=swap" rel="stylesheet">

    <style>
        :root {
            --primary:       #1a6b3a;
            --primary-light: #2d9e5f;
            --primary-pale:  #e8f5ee;
            --sidebar-width: 260px;
            --navbar-height: 60px;
        }

        * { font-family: 'Plus Jakarta Sans', sans-serif; }
        body { background: #f4f7f5; }

        /* ── SIDEBAR ── */
        #sidebar {
            width: var(--sidebar-width);
            min-height: 100vh;
            background: linear-gradient(180deg, #0f4024 0%, #1a6b3a 60%, #2d9e5f 100%);
            position: fixed;
            top: 0; left: 0;
            z-index: 1000;
            transition: transform .3s ease;
            display: flex;
            flex-direction: column;
        }
        #sidebar .sidebar-brand {
            padding: 1.25rem 1.5rem;
            border-bottom: 1px solid rgba(255,255,255,0.12);
        }
        #sidebar .sidebar-brand h5 {
            color: #fff;
            font-weight: 700;
            margin: 0;
            font-size: .95rem;
            line-height: 1.3;
        }
        #sidebar .sidebar-brand small {
            color: rgba(255,255,255,.55);
            font-size: .72rem;
        }
        #sidebar .nav-section {
            padding: .75rem 1rem .25rem;
            font-size: .68rem;
            font-weight: 700;
            letter-spacing: .08em;
            color: rgba(255,255,255,.4);
            text-transform: uppercase;
        }
        #sidebar .nav-link {
            color: rgba(255,255,255,.75);
            padding: .55rem 1.25rem;
            border-radius: 8px;
            margin: .1rem .75rem;
            font-size: .85rem;
            font-weight: 500;
            display: flex;
            align-items: center;
            gap: .65rem;
            transition: all .2s;
        }
        #sidebar .nav-link:hover,
        #sidebar .nav-link.active {
            background: rgba(255,255,255,.15);
            color: #fff;
        }
        #sidebar .nav-link.active {
            background: rgba(255,255,255,.2);
            font-weight: 600;
        }
        #sidebar .nav-link i { width: 18px; text-align: center; }

        /* ── MAIN CONTENT ── */
        #main-wrapper {
            margin-left: var(--sidebar-width);
            min-height: 100vh;
            display: flex;
            flex-direction: column;
        }

        /* ── NAVBAR ── */
        #topnav {
            height: var(--navbar-height);
            background: #fff;
            border-bottom: 1px solid #e8ede9;
            display: flex;
            align-items: center;
            padding: 0 1.5rem;
            position: sticky;
            top: 0;
            z-index: 999;
            gap: 1rem;
        }
        #topnav .page-title {
            font-weight: 700;
            font-size: 1rem;
            color: #1a2e1d;
        }
        #topnav .topnav-right {
            margin-left: auto;
            display: flex;
            align-items: center;
            gap: 1rem;
        }
        .avatar {
            width: 36px; height: 36px;
            background: var(--primary);
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            color: #fff;
            font-weight: 700;
            font-size: .85rem;
        }

        /* ── PAGE CONTENT ── */
        .page-content {
            padding: 1.75rem;
            flex: 1;
        }

        /* ── CARDS ── */
        .stat-card {
            border: none;
            border-radius: 14px;
            padding: 1.25rem 1.4rem;
            display: flex;
            align-items: center;
            gap: 1rem;
            box-shadow: 0 2px 12px rgba(0,0,0,.06);
            background: #fff;
            transition: transform .2s, box-shadow .2s;
        }
        .stat-card:hover {
            transform: translateY(-2px);
            box-shadow: 0 6px 20px rgba(0,0,0,.1);
        }
        .stat-icon {
            width: 52px; height: 52px;
            border-radius: 12px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.4rem;
            flex-shrink: 0;
        }
        .stat-card .stat-label {
            font-size: .75rem;
            color: #7a9280;
            font-weight: 500;
            margin-bottom: .15rem;
        }
        .stat-card .stat-value {
            font-size: 1.45rem;
            font-weight: 700;
            color: #1a2e1d;
            line-height: 1;
        }
        .stat-card .stat-sub {
            font-size: .72rem;
            color: #adb5bd;
            margin-top: .2rem;
        }

        /* ── PROGRESS GIZI ── */
        .progress { height: 10px; border-radius: 6px; }
        .card-mbg {
            border: none;
            border-radius: 14px;
            box-shadow: 0 2px 12px rgba(0,0,0,.06);
        }
        .card-mbg .card-header {
            background: transparent;
            border-bottom: 1px solid #eef2ef;
            font-weight: 700;
            font-size: .9rem;
            color: #1a2e1d;
            padding: 1rem 1.25rem .75rem;
        }

        /* ── BUDGET BADGE ── */
        .badge-budget-aman    { background: #e8f5ee; color: #1a6b3a; }
        .badge-budget-warning { background: #fff8e1; color: #f57c00; }
        .badge-budget-over    { background: #fce4e4; color: #c62828; }

        /* ── RESPONSIVE ── */
        @media (max-width: 768px) {
            #sidebar { transform: translateX(-100%); }
            #sidebar.show { transform: translateX(0); }
            #main-wrapper { margin-left: 0; }
        }
    </style>

    @stack('styles')
</head>
<body>

{{-- SIDEBAR --}}
@include('partials.sidebar')

{{-- OVERLAY mobile --}}
<div id="sidebarOverlay" onclick="closeSidebar()"
     style="display:none; position:fixed; inset:0; background:rgba(0,0,0,.4); z-index:999;"></div>

{{-- MAIN WRAPPER --}}
<div id="main-wrapper">

    {{-- NAVBAR --}}
    @include('partials.navbar')

    {{-- PAGE CONTENT --}}
    <div class="page-content">
        @yield('content')
    </div>

    {{-- FOOTER --}}
    <footer class="text-center py-3" style="font-size:.75rem; color:#adb5bd;">
        &copy; {{ date('Y') }} Dashboard MBG — SPPG Monitoring System
    </footer>
</div>

{{-- Bootstrap JS --}}
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
<script>
    function closeSidebar() {
        document.getElementById('sidebar').classList.remove('show');
        document.getElementById('sidebarOverlay').style.display = 'none';
    }
    function toggleSidebar() {
        const s = document.getElementById('sidebar');
        const o = document.getElementById('sidebarOverlay');
        const open = s.classList.toggle('show');
        o.style.display = open ? 'block' : 'none';
    }
</script>

<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.min.js"></script>

@stack('scripts')
</body>
</html>