@php
    // Admin pages (an /admin/... address while logged in) get the admin navbar.
    $isAdminNav = request()->is('admin/*') && auth()->check();

    // No footer on any /admin/... page, including the admin login page.
    $hideFooter = request()->is('admin/*');

    // Unread counts for the red badges in the admin navbar (contacts.is_read = 0, bookings.is_read = 0).
    // Only counted on admin pages, so public pages run no extra queries. 100 or more is shown as "99+".
    $unreadMessages = $isAdminNav ? \App\Models\Contact::where('is_read', false)->count() : 0;
    $unreadBookings = $isAdminNav ? \App\Models\Booking::where('is_read', false)->count() : 0;

    // The red badge HTML for a count ('' when the count is 0, so the badge is hidden completely).
    // The number is cast to int, so it is safe to print unescaped.
    $unreadBadge = fn (int $count) => $count > 0
        ? '<span class="nav-badge">' . ($count > 99 ? '99+' : $count) . '<span class="visually-hidden"> unread</span></span>'
        : '';

    // Branding logo (Admin > Banner Settings > Branding). $brandLogo is its URL, or null when there is no
    // logo or the file is missing, so a broken image is never shown.
    $brandSetting = \App\Models\BannerSetting::first();
    $brandLogoPath = $brandSetting?->logo;
    $brandLogo = $brandLogoPath && \Illuminate\Support\Facades\Storage::disk('public')->exists($brandLogoPath) ? asset('storage/' . $brandLogoPath) : null;

    // Brand name (Admin > Banner Settings > Branding). Empty means the default "Eocambo Tours".
    // It is shown in two colours: everything before the last word in white, the last word in gold.
    $brandName = trim((string) $brandSetting?->brand_name) ?: 'Eocambo Tours';
    $brandWords = preg_split('/\s+/', $brandName);
    $brandLast = count($brandWords) > 1 ? array_pop($brandWords) : '';
    $brandFirst = implode(' ', $brandWords);
@endphp
<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>@yield('title', 'Eocambo Tours')</title>

    {{-- Bootstrap 5 CSS --}}
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">

    {{-- Navbar and footer styles (blue and gold, same colors as the home banner) --}}
    <style>
        .site-nav {
            background: linear-gradient(90deg, #00529B, #0A6CC0);
            border-bottom: 3px solid #FFB81C;
            box-shadow: 0 2px 10px rgba(0, 0, 0, .2);
        }
        .site-nav .navbar-brand { display: flex; align-items: center; gap: .6rem; font-weight: 800; letter-spacing: .03em; font-size: 1.35rem; }
        .site-nav .navbar-brand .brand-name span { color: #FFB81C; }
        /* A long brand name may wrap onto several lines instead of pushing the page wider than the screen */
        .site-nav .navbar-brand { min-width: 0; white-space: normal; }
        .brand-name { min-width: 0; overflow-wrap: anywhere; }
        @media (max-width: 991.98px) {
            .site-nav .navbar-brand { flex: 1 1 0; margin-right: .75rem; line-height: 1.2; }
        }
        /* Circular branding logo (used in the navbar and footer) */
        .brand-logo { flex: none; border-radius: 50%; object-fit: cover; background: #fff; }
        .brand-logo-nav { width: 38px; height: 38px; box-shadow: 0 0 0 2px rgba(255, 255, 255, .6); }
        .site-nav .nav-link { position: relative; color: rgba(255, 255, 255, .85); font-weight: 600; padding: .6rem 1rem; }
        .site-nav .nav-link:hover, .site-nav .nav-link:focus { color: #fff; }
        .site-nav .nav-link.active { color: #FFB81C; }
        /* Gold underline that slides in on hover and stays on the current page */
        .site-nav .nav-link::after { content: ""; position: absolute; left: 1rem; right: 1rem; bottom: .2rem; height: 2px; background: #FFB81C; transform: scaleX(0); transition: transform .2s; }
        .site-nav .nav-link:hover::after, .site-nav .nav-link.active::after { transform: scaleX(1); }
        .site-nav .navbar-toggler { border-color: rgba(255, 255, 255, .5); }
        /* Admin navbar: small red badge with the number of unread Messages / Bookings (red is used ONLY for these badges) */
        .site-nav .nav-badge { display: inline-block; min-width: 1.25rem; height: 1.25rem; margin-left: .4rem; padding: 0 .3rem; border-radius: 50rem; background: #dc3545; color: #fff; font-size: .75rem; font-weight: 700; line-height: 1.25rem; text-align: center; vertical-align: text-top; }
        /* Admin navbar: gold outline Logout button */
        .site-nav .nav-logout { background: transparent; color: #FFB81C; border: 2px solid #FFB81C; border-radius: 50rem; font-weight: 700; padding: .35rem 1.25rem; transition: background .2s, color .2s; }
        .site-nav .nav-logout:hover, .site-nav .nav-logout:focus-visible { background: #FFB81C; color: #00335f; }
        @media (max-width: 991.98px) {
            .site-nav .navbar-collapse { padding: .5rem 0 .75rem; }
            .site-nav .nav-link::after { left: 0; right: auto; width: 2.5rem; }
        }

        /* ---------- Page body (everything between the navbar and the footer) ---------- */
        body { background: #f4f8fc; }
        main { --bs-heading-color: #00335f; }

        /* Section titles get a small gold bar underneath */
        main h1.text-center::after, main h2.text-center::after { content: ""; display: block; width: 60px; height: 4px; margin: .6rem auto 0; border-radius: 2px; background: #FFB81C; }

        /* Cards: soft, rounded, and a gentle lift when the whole card is a link */
        main .card { border: 0; border-radius: 1rem; overflow: hidden; }
        main .card-header, main .card-footer { background: #eef5fc; border-color: #dbe8f5; }
        main a.card, main .package-card { transition: transform .2s, box-shadow .2s; }
        main a.card:hover, main .package-card:hover { transform: translateY(-4px); box-shadow: 0 .75rem 1.5rem rgba(0, 51, 95, .18) !important; }
        main a.card:focus-visible, main .package-card:focus-within { outline: 3px solid #FFB81C; outline-offset: 2px; }

        /* Buttons: rounded and in the brand blue instead of Bootstrap green */
        main .btn { border-radius: 50rem; font-weight: 600; }
        main .btn-success {
            --bs-btn-bg: #00529B; --bs-btn-border-color: #00529B;
            --bs-btn-hover-bg: #003f7a; --bs-btn-hover-border-color: #003f7a;
            --bs-btn-active-bg: #003666; --bs-btn-active-border-color: #003666;
            --bs-btn-disabled-bg: #00529B; --bs-btn-disabled-border-color: #00529B;
            --bs-btn-focus-shadow-rgb: 0, 82, 155;
        }
        main .btn-outline-success, main .btn-outline-primary {
            --bs-btn-color: #00529B; --bs-btn-border-color: #00529B;
            --bs-btn-hover-bg: #00529B; --bs-btn-hover-border-color: #00529B; --bs-btn-hover-color: #fff;
            --bs-btn-active-bg: #003f7a; --bs-btn-active-border-color: #003f7a; --bs-btn-active-color: #fff;
            --bs-btn-focus-shadow-rgb: 0, 82, 155;
        }
        main .text-success { color: #00529B !important; } /* prices */
        main section.bg-success { background: linear-gradient(120deg, #00529B, #0A6CC0) !important; } /* home hero when the banner is hidden */
        main .bg-light { background: #e8f1fa !important; }

        /* Forms */
        main .form-label { font-weight: 600; }
        main .form-control { border-radius: .6rem; }
        main .form-control:focus { border-color: #4DB0FF; box-shadow: 0 0 0 .25rem rgba(77, 176, 255, .25); }
        main .form-check-input:checked { background-color: #00529B; border-color: #00529B; }
        main .form-panel { background: #fff; border-radius: 1rem; padding: 1.75rem; box-shadow: 0 .25rem .75rem rgba(0, 51, 95, .1); }

        /* Admin tables */
        main .table-responsive { background: #fff; border-radius: 1rem; box-shadow: 0 .25rem .75rem rgba(0, 51, 95, .1); }
        main .table thead { --bs-table-bg: #00529B; --bs-table-color: #fff; --bs-table-border-color: #00529B; }
        main .alert { border: 0; border-radius: .75rem; }

        @unless ($hideFooter)
        .site-footer { background: #00335f; color: rgba(255, 255, 255, .8); border-top: 3px solid #FFB81C; }
        .site-footer h6 { color: #FFB81C; font-weight: 800; letter-spacing: .08em; text-transform: uppercase; font-size: .85rem; margin-bottom: .75rem; }
        .site-footer .footer-brand { display: flex; align-items: center; gap: .75rem; color: #fff; font-weight: 800; font-size: 1.35rem; letter-spacing: .03em; }
        .site-footer .footer-brand .brand-name span { color: #FFB81C; }
        .brand-logo-footer { width: 48px; height: 48px; box-shadow: 0 0 0 2px rgba(255, 255, 255, .5); }
        .site-footer ul { list-style: none; padding: 0; margin: 0; }
        .site-footer li { margin-bottom: .35rem; }
        .site-footer a { color: rgba(255, 255, 255, .8); text-decoration: none; }
        .site-footer a:hover, .site-footer a:focus { color: #FFB81C; text-decoration: underline; }
        .site-footer .footer-bottom { border-top: 1px solid rgba(255, 255, 255, .15); color: rgba(255, 255, 255, .6); font-size: .9rem; }
        @endunless
    </style>
</head>
<body class="d-flex flex-column min-vh-100">

    {{-- Navbar --}}
    <nav class="navbar navbar-expand-lg navbar-dark site-nav sticky-top">
        <div class="container">
            <a class="navbar-brand" href="{{ $isAdminNav ? '/admin/dashboard' : '/' }}">
                @if ($brandLogo)
                    <img src="{{ $brandLogo }}" alt="" class="brand-logo brand-logo-nav">
                @endif
                <span class="brand-name">{{ $brandFirst }}@if ($brandLast) <span>{{ $brandLast }}</span>@endif</span>
            </a>

            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#mainNav" aria-controls="mainNav" aria-expanded="false" aria-label="Toggle navigation">
                <span class="navbar-toggler-icon"></span>
            </button>

            <div class="collapse navbar-collapse" id="mainNav">
                @if ($isAdminNav)
                    {{-- Admin navbar: links on the left, Logout (POST form) on the right --}}
                    <ul class="navbar-nav me-auto">
                        <li class="nav-item">
                            <a class="nav-link {{ request()->is('admin/packages*') ? 'active' : '' }}" href="/admin/packages" @if (request()->is('admin/packages*')) aria-current="page" @endif>Packages</a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link {{ request()->is('admin/messages*') ? 'active' : '' }}" href="/admin/messages" @if (request()->is('admin/messages*')) aria-current="page" @endif>Messages{!! $unreadBadge($unreadMessages) !!}</a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link {{ request()->is('admin/bookings*') ? 'active' : '' }}" href="/admin/bookings" @if (request()->is('admin/bookings*')) aria-current="page" @endif>Bookings{!! $unreadBadge($unreadBookings) !!}</a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link {{ request()->is('admin/banner*') ? 'active' : '' }}" href="/admin/banner" @if (request()->is('admin/banner*')) aria-current="page" @endif>Banner Settings</a>
                        </li>
                    </ul>

                    <form action="/admin/logout" method="POST" class="my-2 my-lg-0">
                        @csrf
                        <button type="submit" class="nav-logout">Logout</button>
                    </form>
                @else
                    {{-- Public navbar --}}
                    <ul class="navbar-nav ms-auto">
                        <li class="nav-item">
                            <a class="nav-link {{ request()->is('/') ? 'active' : '' }}" href="/" @if (request()->is('/')) aria-current="page" @endif>Home</a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link {{ request()->is('packages*') ? 'active' : '' }}" href="/packages" @if (request()->is('packages*')) aria-current="page" @endif>Package</a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link {{ request()->is('contact') ? 'active' : '' }}" href="/contact" @if (request()->is('contact')) aria-current="page" @endif>Contact</a>
                        </li>
                    </ul>
                @endif
            </div>
        </div>
    </nav>

    {{-- Page content --}}
    <main class="flex-grow-1">
        @yield('content')
    </main>

    {{-- Footer (public pages only) --}}
    @unless ($hideFooter)
    <footer class="site-footer">
        <div class="container pt-4 pb-3">
            <div class="row g-4">
                <div class="col-md-7">
                    <div class="footer-brand mb-2">
                        @if ($brandLogo)
                            <img src="{{ $brandLogo }}" alt="" class="brand-logo brand-logo-footer">
                        @endif
                        <span class="brand-name">{{ $brandFirst }}@if ($brandLast) <span>{{ $brandLast }}</span>@endif</span>
                    </div>
                    <p class="mb-0" style="max-width: 420px;">Explore ancient temples, beautiful beaches and friendly local life in the Kingdom of Wonder, with guides who love their country.</p>
                </div>

                <div class="col-md-5">
                    <h6>Quick Links</h6>
                    <ul>
                        <li><a href="/">Home</a></li>
                        <li><a href="/packages">Tour Packages</a></li>
                        <li><a href="/booking">Book a Tour</a></li>
                        <li><a href="/contact">Contact Us</a></li>
                    </ul>
                </div>
            </div>

            <div class="footer-bottom text-center mt-4 pt-3">
                &copy; {{ date('Y') }} {{ $brandName }}. All rights reserved.
            </div>
        </div>
    </footer>
    @endunless

    {{-- Bootstrap 5 JavaScript --}}
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
