<title>PDV Connect</title>
<meta charset="utf-8" />
<meta name="csrf-token" content="{{ csrf_token() }}">
<meta content="follow, index" name="robots" />
<link href="{{ url(request()->path()) }}" rel="canonical" />
<meta content="width=device-width, initial-scale=1, shrink-to-fit=no" name="viewport" />
<meta content="" name="description" />
<meta content="@keenthemes" name="twitter:site" />
<meta content="@keenthemes" name="twitter:creator" />
<meta content="summary_large_image" name="twitter:card" />
<meta content="PDV Connect" name="twitter:title" />
<meta content="" name="twitter:description" />
<meta content="{{ asset('assets/media/app/og-image.png') }}" name="twitter:image" />
<meta content="{{ url(request()->path()) }}" property="og:url" />
<meta content="en_US" property="og:locale" />
<meta content="website" property="og:type" />
<meta content="@keenthemes" property="og:site_name" />
<meta content="PDV Connect" property="og:title" />
<meta content="" property="og:description" />
<meta content="{{ asset('assets/media/app/og-image.png') }}" property="og:image" />
<link href="{{ asset('assets/media/app/favicon.svg') }}" rel="icon" type="image/svg+xml" />
<link href="{{ asset('assets/media/app/apple-touch-icon.png') }}" rel="apple-touch-icon" sizes="180x180" />
<link href="{{ asset('assets/media/app/favicon-32x32.png') }}" rel="icon" sizes="32x32" type="image/png" />
<link href="{{ asset('assets/media/app/favicon-16x16.png') }}" rel="icon" sizes="16x16" type="image/png" />
<link href="{{ asset('assets/media/app/favicon.ico') }}" rel="shortcut icon" />
<link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&family=Geist+Sans:wght@400;500;600;700;800&display=swap" rel="stylesheet" />
<link href="{{ asset('assets/vendors/apexcharts/apexcharts.css') }}" rel="stylesheet" />
<link href="{{ asset('assets/vendors/keenicons/styles.bundle.css') }}" rel="stylesheet" />
<link href="{{ asset('assets/css/styles.css') }}" rel="stylesheet" />
@auth
<style>
    #sidebar_menu.kt-permissions-loading > :not(.kt-sidebar-menu-skeleton) { display: none !important; }
    #sidebar_menu.kt-permissions-loading .kt-sidebar-menu-skeleton { display: flex !important; }
    .kt-sidebar-menu-skeleton { display: none; flex-direction: column; gap: 0.5rem; padding: 0.25rem 0.5rem; }
    .kt-sidebar-menu-skeleton span { display: block; height: 2.25rem; border-radius: 0.375rem; background: linear-gradient(90deg, rgba(148,163,184,.15) 25%, rgba(148,163,184,.28) 50%, rgba(148,163,184,.15) 75%); background-size: 200% 100%; animation: kt-menu-skeleton 1.2s ease-in-out infinite; }
    @keyframes kt-menu-skeleton { 0% { background-position: 200% 0; } 100% { background-position: -200% 0; } }
</style>
@endauth
