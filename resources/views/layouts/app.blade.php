<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>SI-BK Digital - @yield('title', 'Dashboard')</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <script>
        tailwind.config = { theme: { extend: { colors: {
            brand: { 50:'#fff7f9',100:'#fce7ed',200:'#f7cbd8',300:'#efa9bf',500:'#d97898',600:'#c75d80',700:'#a94466',900:'#713047' }
        }}}}
    </script>
    <style>
        :root {
            color-scheme: light;
            --sidebar-width: 17rem;
            --ink: #292429;
            --muted-ink: #5d555b;
            --pink-bg: #fff8fa;
            --pink-soft: #fce9ef;
            --pink-border: #f2d7e0;
            --pink-accent: #a94466;
        }
        body { background: #fff9fb; color: var(--ink); }
        input, select, textarea { border-radius: .65rem; }
        input:focus, select:focus, textarea:focus { outline: 2px solid #f7cbd8; outline-offset: 1px; border-color: #d97898; }
        table thead { color: #40363c; }
        table tbody tr:hover { background-color: #fff8fa; }
        .app-sidebar {
            position: fixed; inset: 0 auto 0 0; z-index: 50; display: flex; width: var(--sidebar-width);
            flex-direction: column; border-right: 1px solid var(--pink-border); background: #fff;
            box-shadow: 8px 0 28px rgba(73, 39, 53, .045);
        }
        .sidebar-brand { display: flex; min-height: 5.25rem; align-items: center; gap: .75rem; padding: 1rem 1.25rem; border-bottom: 1px solid #f6e8ed; }
        .brand-mark { display: grid; width: 2.65rem; height: 2.65rem; flex: none; place-items: center; border-radius: 1rem; background: var(--pink-soft); color: var(--pink-accent); }
        .sidebar-scroll { flex: 1; overflow-y: auto; padding: 1.1rem .85rem; }
        .menu-group + .menu-group { margin-top: 1.25rem; }
        .menu-heading { margin: 0 0 .4rem; padding: 0 .75rem; color: #71666d; font-size: .66rem; font-weight: 750; letter-spacing: .12em; }
        .menu-link { position: relative; display: flex; min-height: 2.55rem; align-items: center; gap: .75rem; margin-top: .3rem; border-radius: .75rem; padding: .55rem .75rem; color: #393238; font-size: .875rem; font-weight: 550; text-decoration: none; transition: background-color .18s ease, color .18s ease; }
        .menu-link:hover { background: #fff5f8; color: #78364f; }
        .menu-link.is-active { background: #fce9ef; color: #792e4a; font-weight: 700; }
        .menu-link.is-active::before { position: absolute; inset: .5rem auto .5rem 0; width: 3px; border-radius: 0 4px 4px 0; background: #c75d80; content: ""; }
        .menu-icon { display: grid; width: 1.35rem; height: 1.35rem; flex: none; place-items: center; color: #776a72; }
        .menu-icon svg { width: 1.2rem; height: 1.2rem; }
        .menu-link:hover .menu-icon, .menu-link.is-active .menu-icon { color: #a94466; }
        .sidebar-account { border-top: 1px solid #f2e6eb; padding: .9rem; }
        .account-card { display: flex; align-items: center; gap: .7rem; padding: .5rem .35rem .75rem; }
        .account-avatar { display: grid; width: 2.4rem; height: 2.4rem; flex: none; place-items: center; border-radius: 50%; background: #fce9ef; color: #792e4a; font-weight: 750; }
        .account-name { overflow: hidden; color: #302a2e; font-size: .85rem; font-weight: 700; text-overflow: ellipsis; white-space: nowrap; }
        .account-caption { margin-top: .1rem; color: #6d6268; font-size: .72rem; }
        .logout-button { width: 100%; border: 1px solid #f0dce3; border-radius: .75rem; background: #fff8fa; padding: .6rem .8rem; color: #72364d; font-size: .82rem; font-weight: 650; text-align: left; }
        .logout-button:hover { background: #fce9ef; }
        .app-content { min-height: 100vh; min-width: 0; margin-left: var(--sidebar-width); }
        .mobile-bar { display: none; }
        .sidebar-overlay { display: none; }
        @media (max-width: 767px) {
            .app-sidebar { visibility: hidden; transform: translateX(-105%); transition: transform .22s ease, visibility .22s ease; }
            body.sidebar-open .app-sidebar { visibility: visible; transform: translateX(0); }
            .sidebar-overlay { position: fixed; inset: 0; z-index: 40; background: rgba(36, 27, 32, .38); }
            body.sidebar-open .sidebar-overlay { display: block; }
            .app-content { margin-left: 0; }
            .mobile-bar { position: sticky; top: 0; z-index: 30; display: flex; min-height: 3.75rem; align-items: center; gap: .75rem; border-bottom: 1px solid var(--pink-border); background: rgba(255,255,255,.96); padding: .65rem 1rem; backdrop-filter: blur(10px); }
            .mobile-menu-button { display: grid; width: 2.45rem; height: 2.45rem; place-items: center; border: 1px solid #f0dce3; border-radius: .75rem; background: #fff8fa; color: #542d3d; }
            .app-content main { min-width: 0; padding: 1rem; }
            .app-content main table { display: block; width: 100%; max-width: 100%; overflow-x: auto; -webkit-overflow-scrolling: touch; }
            .app-content main :where(input, select, textarea) { max-width: 100%; font-size: 16px; }
            .app-content main :where(form, section, article) { min-width: 0; }
            .app-content main :where(h1, h2, h3) { overflow-wrap: anywhere; }
        }
        @media (prefers-reduced-motion: reduce) {
            *, *::before, *::after { scroll-behavior: auto !important; transition-duration: .01ms !important; }
        }
    </style>
</head>
<body class="min-h-screen">
@php
    // Daftar menu disusun per role di sini (satu tempat). Format: [label, nama route, pola route aktif].
    // Ingat: ini HANYA mengatur tampilan menu. Pembatasan akses yang sebenarnya ada di routes/web.php (middleware role).
    $u = auth()->user();
    $staf = $u->isAdmin() || $u->isGuruBk();

    $menu = [];
    if ($u->isSiswa()) {
        $menu[] = ['Booking Konseling', 'booking-siswa.index', 'booking-siswa.*'];
    } else {
        $menu = [['Dashboard', 'dashboard', 'dashboard'], ['Data Siswa', 'siswa.index', 'siswa.*']];
        if ($staf) { $menu[] = ['Kelas', 'kelas.index', 'kelas.*']; $menu[] = ['Daftar Pelanggaran', 'jenis-pelanggaran.index', 'jenis-pelanggaran.*']; $menu[] = ['Absensi', 'absensi.index', 'absensi.*']; }
        elseif ($u->isKepalaSekolah()) { $menu[] = ['Absensi', 'absensi.rekap', 'absensi.*']; }
        if (! $u->isWaliKelas()) { $menu[] = ['Poin Siswa', 'poin.index', 'poin.*']; }
        if ($u->isGuruBk() || $u->isWaliKelas()) { $menu[] = ['Anekdot', 'anekdot.index', 'anekdot.*']; }
        if ($staf) { $menu[] = ['Asesmen', 'asesmen.link.index', 'asesmen.*']; $menu[] = ['Surat', 'surat.index', 'surat.*']; }
        if ($u->isGuruBk()) {
            $menu[] = ['Profil Saya', 'profil-guru-bk.edit', 'profil-guru-bk.*'];
            $menu[] = ['Program Tahunan', 'program-tahunan.index', 'program-tahunan.*'];
            $menu[] = ['Program Semester', 'program-semester.index', 'program-semester.*'];
            $menu[] = ['RPL', 'rpl.index', 'rpl.*'];
            $menu[] = ['Laporan Layanan BK', 'laporan.konseling-individual', 'laporan.*'];
            $menu[] = ['Jurnal Harian', 'jurnal.index', 'jurnal.index'];
            $menu[] = ['Jurnal Bulanan', 'jurnal.bulanan.index', 'jurnal.bulanan.*'];
            $menu[] = ['Home Visit', 'homevisit.index', 'homevisit.*'];
            $menu[] = ['Instrumen BK', 'instrumen.index', 'instrumen.*'];
            $menu[] = ['Formulir Layanan BK', 'bk-formulir.index', 'bk-formulir.*'];
            $menu[] = ['Ajuan Konseling', 'booking-staf.index', 'booking-staf.*'];
        }
        if ($staf) { $menu[] = ['Jadwal', 'jadwal.index', 'jadwal.*']; }
        if ($staf || $u->isKepalaSekolah()) { $menu[] = ['Alumni', 'alumni.index', 'alumni.*']; }
        if ($staf) { $menu[] = ['Audit Log', 'audit.index', 'audit.*']; }
        if ($staf) { $menu[] = ['Rapor BK per Siswa', 'laporan.siswa-bk.index', 'laporan.siswa-bk.*']; }
        if ($staf) { $menu[] = ['Akreditasi & Supervisi', 'bk-akreditasi.index', 'bk-akreditasi.*']; }
        if ($staf) { $menu[] = ['Sekolah', 'sekolah.edit', 'sekolah.*']; }
        if ($u->isAdmin()) { $menu[] = ['Pengguna', 'pengguna.index', 'pengguna.*']; }
    }

    $menuGroups = [
        'UTAMA' => ['Dashboard', 'Booking Konseling'],
        'DATABASE' => ['Sekolah', 'Kelas', 'Data Siswa', 'Daftar Pelanggaran'],
        'PROFIL & LAYANAN' => ['Profil Saya', 'Anekdot', 'Formulir Layanan BK', 'Home Visit'],
        'PROGRAM' => ['Program Tahunan', 'Program Semester', 'RPL', 'Asesmen', 'Instrumen BK', 'Surat'],
        'LAPORAN & EVALUASI' => ['Laporan Layanan BK', 'Rapor BK per Siswa', 'Poin Siswa', 'Absensi', 'Alumni', 'Jurnal Harian', 'Jurnal Bulanan', 'Audit Log', 'Akreditasi & Supervisi'],
        'JADWAL KONSELING' => ['Jadwal', 'Ajuan Konseling'],
        'PENGELOLAAN AKUN' => ['Pengguna'],
    ];
    $menuGroups = array_map(
        fn ($labels) => array_values(array_filter($menu, fn ($item) => in_array($item[0], $labels, true))),
        $menuGroups
    );
    $displayName = $u->name ?? 'Pengguna';
    $menuIcons = [
        'Dashboard' => 'M3 3h7v7H3z M14 3h7v4h-7z M14 10h7v11h-7z M3 13h7v8H3z',
        'Sekolah' => 'M3 21h18 M5 21V7l7-4 7 4v14 M9 9h.01 M15 9h.01 M9 13h.01 M15 13h.01 M10 21v-4h4v4',
        'Kelas' => 'M3 5h18v14H3z M3 9h18 M8 5v14 M14 5v14',
        'Data Siswa' => 'M16 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2 M10 11a4 4 0 1 0 0-8 4 4 0 0 0 0 8 M20 21v-2a4 4 0 0 0-3-3.87 M16 3.13a4 4 0 0 1 0 7.75',
        'Daftar Pelanggaran' => 'M9 12l2 2 4-4 M12 3l8 4v5c0 5-3.4 8-8 9-4.6-1-8-4-8-9V7l8-4z',
        'Profil Saya' => 'M20 21a8 8 0 0 0-16 0 M12 13a4 4 0 1 0 0-8 4 4 0 0 0 0 8',
        'Anekdot' => 'M4 4h16v13H8l-4 4z M8 9h8 M8 13h5',
        'Formulir Layanan BK' => 'M7 3h10l4 4v14H7z M17 3v5h4 M10 12h5 M10 16h5 M3 7v14',
        'Home Visit' => 'M3 11l9-8 9 8 M5 10v10h14V10 M9 20v-6h6v6',
        'Program Tahunan' => 'M4 19.5A2.5 2.5 0 0 1 6.5 17H20 M6.5 2H20v20H6.5A2.5 2.5 0 0 1 4 19.5v-15A2.5 2.5 0 0 1 6.5 2z',
        'Program Semester' => 'M7 3v3 M17 3v3 M4 7h16v14H4z M8 11h2 M14 11h2 M8 15h2 M14 15h2',
        'RPL' => 'M4 4h16v16H4z M8 8h8 M8 12h8 M8 16h5',
        'Laporan Layanan BK' => 'M4 4h16v16H4z M8 8h8 M8 12h8 M8 16h5',
        'Rapor BK per Siswa' => 'M4 4h16v16H4z M8 8h8 M8 12h5 M8 16h8 M16 12h2',
        'Asesmen' => 'M9 11l2 2 4-4 M5 3h14v18H5z M8 3V2 M16 3V2',
        'Instrumen BK' => 'M4 4h16v16H4z M8 8h8 M8 12h8 M8 16h5',
        'Surat' => 'M4 4h16v16H4z M4 7l8 6 8-6',
        'Poin Siswa' => 'M12 3v18 M3 12h18 M5 5l14 14 M19 5L5 19',
        'Absensi' => 'M7 3v3 M17 3v3 M4 7h16v14H4z M8 12h3 M8 16h3 M14 12h2',
        'Alumni' => 'M16 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2 M10 11a4 4 0 1 0 0-8 4 4 0 0 0 0 8 M16 11l2 2 4-4',
        'Jurnal Harian' => 'M5 3h14v18H5z M8 7h8 M8 11h8 M8 15h8 M8 19h5',
        'Jurnal Bulanan' => 'M7 3v3 M17 3v3 M4 7h16v14H4z M8 11h2 M14 11h2 M8 15h2 M14 15h2',
        'Audit Log' => 'M3 12a9 9 0 1 0 2.6-6.4L3 8 M3 3v5h5 M12 7v5l3 2',
        'Akreditasi & Supervisi' => 'M4 4h16v16H4z M8 8h8 M8 12h5 M8 16h8 M16 12l1.5 1.5L20 11',
        'Jadwal' => 'M7 3v3 M17 3v3 M4 7h16v14H4z M8 11h2 M14 11h2 M8 15h2 M14 15h2',
        'Pengguna' => 'M16 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2 M10 11a4 4 0 1 0 0-8 4 4 0 0 0 0 8 M17 8h4 M19 6v4',
        'Booking Konseling' => 'M8 2v4 M16 2v4 M3 10h18 M5 4h14a2 2 0 0 1 2 2v13H3V6a2 2 0 0 1 2-2z M8 14h3',
        'Ajuan Konseling' => 'M4 4h16v16H4z M8 8h8 M8 12h5 M8 16h3 M17 14l2 2 3-4',
    ];
    $roleLabel = match ($u->role) {
        'admin' => 'Administrator',
        'guru_bk' => 'Guru BK',
        'wali_kelas' => 'Wali Kelas',
        'kepala_sekolah' => 'Kepala Sekolah',
        'siswa' => 'Siswa',
        default => 'Pengguna SI-BK Digital',
    };
@endphp
    <aside id="app-sidebar" class="app-sidebar" aria-label="Navigasi utama">
        <a href="{{ $u->isSiswa() ? route('booking-siswa.index') : route('dashboard') }}" class="sidebar-brand no-underline">
            <span class="brand-mark" aria-hidden="true">
                <svg viewBox="0 0 24 24" width="22" height="22" fill="none" stroke="currentColor" stroke-width="1.8">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 3.5c1.3 2.2 2.1 3.4 4.2 3.8-1.2 1.7-1.4 3.2-.2 5.1-2.1-.1-3.2.6-4 2.8-.8-2.2-1.9-2.9-4-2.8 1.2-1.9 1-3.4-.2-5.1C9.9 6.9 10.7 5.7 12 3.5Z"/>
                    <path stroke-linecap="round" d="M12 15.5v5M9.5 20.5h5"/>
                </svg>
            </span>
            <span>
                <span class="block text-base font-bold leading-tight text-slate-900">SI-BK Digital</span>
                <span class="mt-1 block text-xs leading-tight text-slate-600">Administrasi Bimbingan & Konseling</span>
            </span>
        </a>

        <nav class="sidebar-scroll" aria-label="Menu aplikasi">
            @foreach($menuGroups as $heading => $items)
                @if(count($items))
                    <section class="menu-group" aria-label="{{ $heading }}">
                        <h2 class="menu-heading">{{ $heading }}</h2>
                        @foreach($items as $item)
                            @php
                                $active = request()->routeIs($item[2])
                                    && !($item[0] === 'Laporan Layanan BK' && request()->routeIs('laporan.siswa-bk.*'));
                                $menuRouteParameters = [];
                            @endphp
                            <a href="{{ route($item[1], $menuRouteParameters) }}"
                               @if($active) aria-current="page" @endif
                               class="menu-link {{ $active ? 'is-active' : '' }}">
                                <span class="menu-icon" aria-hidden="true">
                                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.7" stroke-linecap="round" stroke-linejoin="round">
                                        <path d="{{ $menuIcons[$item[0]] ?? $menuIcons['Dashboard'] }}"/>
                                    </svg>
                                </span>
                                <span>{{ $item[0] }}</span>
                            </a>
                        @endforeach
                    </section>
                @endif
            @endforeach
        </nav>

        <div class="sidebar-account">
            <div class="account-card">
                <span class="account-avatar" aria-hidden="true">{{ \Illuminate\Support\Str::upper(\Illuminate\Support\Str::substr($displayName, 0, 1)) }}</span>
                <div class="min-w-0">
                    <p class="account-name">{{ $displayName }}</p>
                    <p class="account-caption">{{ $roleLabel }}</p>
                </div>
            </div>
            <form method="POST" action="{{ route('logout') }}">
                @csrf
                <button type="submit" class="logout-button">Keluar dari akun</button>
            </form>
        </div>
    </aside>

    <button type="button" id="sidebar-overlay" class="sidebar-overlay" aria-label="Tutup navigasi"></button>
    <div class="app-content">
        <div class="mobile-bar">
            <button type="button" id="sidebar-toggle" class="mobile-menu-button" aria-label="Buka navigasi" aria-controls="app-sidebar" aria-expanded="false">
                <svg viewBox="0 0 24 24" width="21" height="21" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true">
                    <path stroke-linecap="round" d="M4 6h16M4 12h16M4 18h16"/>
                </svg>
            </button>
            <span class="font-semibold text-slate-900">SI-BK Digital</span>
        </div>
        <main class="mx-auto max-w-7xl px-4 py-6 md:px-6 md:py-8">
            @yield('content')
        </main>
    </div>
    <script>
        (() => {
            const toggle = document.getElementById('sidebar-toggle');
            const overlay = document.getElementById('sidebar-overlay');
            if (!toggle || !overlay) return;

            const setSidebarOpen = (open) => {
                document.body.classList.toggle('sidebar-open', open);
                toggle.setAttribute('aria-expanded', String(open));
                toggle.setAttribute('aria-label', open ? 'Tutup navigasi' : 'Buka navigasi');
            };

            toggle.addEventListener('click', () => setSidebarOpen(!document.body.classList.contains('sidebar-open')));
            overlay.addEventListener('click', () => setSidebarOpen(false));
            document.addEventListener('keydown', (event) => {
                if (event.key === 'Escape') setSidebarOpen(false);
            });
            document.querySelectorAll('#app-sidebar a').forEach((link) => {
                link.addEventListener('click', () => setSidebarOpen(false));
            });
            window.addEventListener('resize', () => {
                if (window.innerWidth > 767) setSidebarOpen(false);
            });
        })();
    </script>
</body>
</html>
