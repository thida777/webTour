{{-- Editable travel banner for the Home page. Content comes from the banner_settings table ($banner). --}}
@php
    // Only real, non-empty social links are shown.
    $socials = collect([
        'Facebook' => $banner->facebook_url,
        'Instagram' => $banner->instagram_url,
        'YouTube' => $banner->youtube_url,
        'X' => $banner->x_url,
    ])->filter();

    $includes = collect([$banner->include_1, $banner->include_2, $banner->include_3, $banner->include_4])->filter();
    $images = [$banner->image_1, $banner->image_2, $banner->image_3];
    $showOffer = $banner->show_offer && $banner->discount !== null;
@endphp

<style>
    /* Banner-only styles. Every class starts with "eb-" so nothing else on the site is affected. */
    .eb-banner {
        --eb-sky: #4DB0FF;
        --eb-deep: #00529B;
        --eb-gold: #FFB81C;
        position: relative;
        overflow: hidden; /* keeps the tilted photos from causing horizontal scrolling */
        color: #fff;
        --bs-heading-color: #fff; /* the page body uses dark blue headings; the banner headings stay white */
        background: linear-gradient(120deg, var(--eb-deep) 0%, #0A6CC0 55%, var(--eb-sky) 100%);
        padding: 3rem 0 6rem;
    }
    .eb-inner { position: relative; z-index: 1; }

    /* Yellow decorative waves along the bottom */
    .eb-wave { position: absolute; left: 0; bottom: 0; width: 100%; height: 70px; z-index: 0; display: block; }

    /* Left side */
    .eb-logo { width: 80px; height: 80px; border-radius: 50%; object-fit: cover; margin-bottom: 1.25rem; box-shadow: 0 0 0 3px rgba(255, 255, 255, .7); }
    .eb-brand { font-weight: 700; font-size: 1.25rem; letter-spacing: .04em; margin-bottom: 1.25rem; }
    .eb-small { font-size: 1.5rem; font-style: italic; font-weight: 600; margin: 0; }
    .eb-main { font-size: clamp(3.25rem, 9vw, 5.5rem); font-weight: 800; line-height: 1; letter-spacing: .02em; margin: 0; text-shadow: 0 3px 10px rgba(0, 0, 0, .25); }
    .eb-highlight { font-size: clamp(1.9rem, 5vw, 3rem); font-weight: 800; letter-spacing: .12em; color: var(--eb-gold); margin: 0; text-shadow: 0 2px 8px rgba(0, 0, 0, .3); }
    .eb-tagline { font-size: 1.35rem; font-weight: 600; margin: .5rem 0 1.5rem; }
    .eb-btn { display: inline-block; background: var(--eb-gold); color: var(--eb-deep); font-weight: 800; letter-spacing: .08em; padding: .8rem 2.25rem; border-radius: 50rem; text-decoration: none; box-shadow: 0 6px 16px rgba(0, 0, 0, .25); transition: transform .15s, box-shadow .15s; }
    .eb-btn:hover, .eb-btn:focus { color: var(--eb-deep); background: #ffc94d; transform: translateY(-2px); box-shadow: 0 10px 20px rgba(0, 0, 0, .3); }
    .eb-contact { list-style: none; padding: 0; margin: 1.5rem 0 0; }
    .eb-contact li { display: flex; align-items: center; gap: .5rem; margin-bottom: .4rem; word-break: break-word; }
    .eb-contact svg { flex: none; fill: var(--eb-gold); }
    .eb-contact a { color: #fff; text-decoration: none; }
    .eb-contact a:hover { text-decoration: underline; }

    /* Center: photo collage (white frames, slight rotation, shadow) */
    .eb-collage { position: relative; width: 100%; max-width: 380px; aspect-ratio: 1 / 1.15; margin: 0 auto; }
    .eb-photo { position: absolute; width: 64%; padding: 6px; background: #fff; border-radius: 4px; box-shadow: 0 10px 24px rgba(0, 0, 0, .35); }
    .eb-photo img, .eb-photo-empty { display: block; width: 100%; aspect-ratio: 5 / 4; object-fit: cover; }
    .eb-photo-empty { background: linear-gradient(135deg, #cfe8ff, #8cc9ff); }
    .eb-photo-1 { top: 0; left: 0; transform: rotate(-6deg); z-index: 1; }
    .eb-photo-2 { top: 32%; right: 0; transform: rotate(5deg); z-index: 2; }
    .eb-photo-3 { bottom: 0; left: 8%; transform: rotate(-3deg); z-index: 3; }

    /* Right side: includes + offer + social. Dark translucent panels keep the white text readable. */
    .eb-panel { background: rgba(0, 60, 115, .55); border-radius: 1rem; padding: 1.25rem; box-shadow: 0 6px 18px rgba(0, 0, 0, .2); }
    .eb-heading { font-size: 1rem; font-weight: 800; letter-spacing: .1em; text-transform: uppercase; margin: 0 0 .75rem; }
    .eb-includes { list-style: none; padding: 0; margin: 0; }
    .eb-includes li { display: flex; align-items: center; gap: .6rem; margin-bottom: .5rem; font-weight: 600; }
    .eb-includes li::before { content: "\2713"; flex: none; width: 1.5rem; height: 1.5rem; line-height: 1.5rem; text-align: center; border-radius: 50%; background: var(--eb-gold); color: var(--eb-deep); font-weight: 800; font-size: .85rem; }
    .eb-offer { text-align: center; }
    .eb-offer-up { font-size: .9rem; font-weight: 700; letter-spacing: .15em; margin: 0; }
    .eb-offer-num { font-size: clamp(2.5rem, 7vw, 3.5rem); font-weight: 800; line-height: 1.05; color: var(--eb-gold); margin: 0; }
    .eb-offer-suffix { font-size: 1.5rem; font-weight: 800; letter-spacing: .15em; margin: 0; }
    .eb-social { display: flex; flex-wrap: wrap; gap: .6rem; }
    .eb-social a { display: inline-flex; align-items: center; justify-content: center; width: 2.5rem; height: 2.5rem; border-radius: 50%; background: #fff; transition: transform .15s; }
    .eb-social a:hover, .eb-social a:focus { transform: translateY(-2px); }
    .eb-social svg { width: 1.25rem; height: 1.25rem; fill: var(--eb-deep); }

    /* Tablet: smaller photos and tighter spacing */
    @media (max-width: 991.98px) {
        .eb-banner { padding: 2.5rem 0 5.5rem; }
        .eb-collage { max-width: 320px; }
    }
    /* Mobile: everything stacks; keep the collage inside the screen */
    @media (max-width: 575.98px) {
        .eb-banner { padding: 2rem 0 5rem; text-align: center; }
        .eb-collage { max-width: 280px; }
        .eb-contact li, .eb-social { justify-content: center; }
        .eb-includes { display: inline-block; text-align: left; }
        .eb-btn { display: block; padding: .95rem 1rem; }
    }
</style>

<section class="eb-banner" aria-label="Eocambo Tours travel banner">
    <div class="container eb-inner">
        <div class="row g-4 align-items-center">

            {{-- LEFT: logo, headline, button, contact --}}
            <div class="col-12 col-md-6 col-lg-4">
                @if ($banner->logo)
                    <img src="{{ asset('storage/' . $banner->logo) }}" alt="Eocambo Tours logo" class="eb-logo">
                @else
                    <div class="eb-brand">Eocambo Tours</div>
                @endif

                @if ($banner->small_title)
                    <p class="eb-small">{{ $banner->small_title }}</p>
                @endif
                @if ($banner->main_title)
                    <h2 class="eb-main">{{ $banner->main_title }}</h2>
                @endif
                @if ($banner->highlight_text)
                    <p class="eb-highlight">{{ $banner->highlight_text }}</p>
                @endif
                @if ($banner->tagline)
                    <p class="eb-tagline">{{ $banner->tagline }}</p>
                @endif

                @if ($banner->button_text && $banner->button_url)
                    <a href="{{ $banner->button_url }}" class="eb-btn">{{ $banner->button_text }}</a>
                @endif

                @if ($banner->phone || $banner->website)
                    <ul class="eb-contact">
                        @if ($banner->phone)
                            <li>
                                <svg width="20" height="20" viewBox="0 0 24 24" aria-hidden="true"><path d="M6.62 10.79a15.05 15.05 0 0 0 6.59 6.59l2.2-2.2a1 1 0 0 1 1.02-.24c1.12.37 2.33.57 3.57.57a1 1 0 0 1 1 1V20a1 1 0 0 1-1 1A17 17 0 0 1 3 4a1 1 0 0 1 1-1h3.5a1 1 0 0 1 1 1c0 1.25.2 2.45.57 3.57a1 1 0 0 1-.25 1.02l-2.2 2.2z"/></svg>
                                <a href="tel:{{ preg_replace('/[^0-9+]/', '', $banner->phone) }}">{{ $banner->phone }}</a>
                            </li>
                        @endif
                        @if ($banner->website)
                            <li>
                                <svg width="20" height="20" viewBox="0 0 24 24" aria-hidden="true"><path d="M12 2a10 10 0 1 0 0 20 10 10 0 0 0 0-20zm6.93 6h-2.95a15.6 15.6 0 0 0-1.38-3.56A8.03 8.03 0 0 1 18.93 8zM12 4.04c.83 1.2 1.48 2.53 1.91 3.96h-3.82c.43-1.43 1.08-2.76 1.91-3.96zM4.26 14a8.2 8.2 0 0 1 0-4h3.38a16.5 16.5 0 0 0 0 4H4.26zm.81 2h2.95c.32 1.25.78 2.45 1.38 3.56A7.99 7.99 0 0 1 5.07 16zm2.95-8H5.07a7.99 7.99 0 0 1 4.33-3.56A15.6 15.6 0 0 0 8.02 8zM12 19.96A14.7 14.7 0 0 1 10.09 16h3.82A14.7 14.7 0 0 1 12 19.96zM14.34 14H9.66a14.7 14.7 0 0 1 0-4h4.68a14.7 14.7 0 0 1 0 4zm.26 5.56c.6-1.11 1.06-2.31 1.38-3.56h2.95a8.03 8.03 0 0 1-4.33 3.56zM16.36 14a16.5 16.5 0 0 0 0-4h3.38a8.2 8.2 0 0 1 0 4h-3.38z"/></svg>
                                <a href="{{ $banner->website }}" target="_blank" rel="noopener noreferrer">{{ $banner->website }}</a>
                            </li>
                        @endif
                    </ul>
                @endif
            </div>

            {{-- CENTER: three tilted photos --}}
            <div class="col-12 col-md-6 col-lg-4">
                <div class="eb-collage">
                    @foreach ($images as $i => $image)
                        <div class="eb-photo eb-photo-{{ $i + 1 }}">
                            @if ($image)
                                <img src="{{ asset('storage/' . $image) }}" alt="Cambodia travel photo {{ $i + 1 }}" loading="lazy">
                            @else
                                <div class="eb-photo-empty"></div>
                            @endif
                        </div>
                    @endforeach
                </div>
            </div>

            {{-- RIGHT: tour includes, special offer, social links --}}
            <div class="col-12 col-lg-4">
                <div class="row g-3">
                    @if ($includes->isNotEmpty())
                        <div class="col-12 col-md-6 col-lg-12">
                            <div class="eb-panel h-100">
                                <h3 class="eb-heading">OUR TOURS INCLUDE</h3>
                                <ul class="eb-includes">
                                    @foreach ($includes as $item)
                                        <li>{{ $item }}</li>
                                    @endforeach
                                </ul>
                            </div>
                        </div>
                    @endif

                    @if ($showOffer)
                        <div class="col-12 col-md-6 col-lg-12">
                            <div class="eb-panel eb-offer h-100">
                                @if ($banner->offer_title)
                                    <h3 class="eb-heading">{{ $banner->offer_title }}</h3>
                                @endif
                                <p class="eb-offer-up">UP TO</p>
                                <p class="eb-offer-num">{{ $banner->discount }}%</p>
                                @if ($banner->offer_suffix)
                                    <p class="eb-offer-suffix">{{ $banner->offer_suffix }}</p>
                                @endif
                            </div>
                        </div>
                    @endif

                    @if ($socials->isNotEmpty())
                        <div class="col-12">
                            <div class="eb-panel">
                            <h3 class="eb-heading">FOLLOW US</h3>
                            <div class="eb-social">
                                @foreach ($socials as $name => $url)
                                    <a href="{{ $url }}" target="_blank" rel="noopener noreferrer" aria-label="Eocambo Tours on {{ $name }}">
                                        @if ($name === 'Facebook')
                                            <svg viewBox="0 0 24 24" aria-hidden="true"><path d="M24 12.073c0-6.627-5.373-12-12-12s-12 5.373-12 12c0 5.99 4.388 10.954 10.125 11.854v-8.385H7.078v-3.47h3.047V9.43c0-3.007 1.792-4.669 4.533-4.669 1.312 0 2.686.235 2.686.235v2.953H15.83c-1.491 0-1.956.925-1.956 1.874v2.25h3.328l-.532 3.47h-2.796v8.385C19.612 23.027 24 18.062 24 12.073z"/></svg>
                                        @elseif ($name === 'Instagram')
                                            <svg viewBox="0 0 24 24" aria-hidden="true"><path d="M12 2.163c3.204 0 3.584.012 4.85.07 3.252.148 4.771 1.691 4.919 4.919.058 1.265.069 1.645.069 4.849 0 3.205-.012 3.584-.069 4.849-.149 3.225-1.664 4.771-4.919 4.919-1.266.058-1.644.07-4.85.07-3.204 0-3.584-.012-4.849-.07-3.26-.149-4.771-1.699-4.919-4.92-.058-1.265-.07-1.644-.07-4.849 0-3.204.013-3.583.07-4.849.149-3.227 1.664-4.771 4.919-4.919 1.266-.057 1.645-.069 4.849-.069zM12 0C8.741 0 8.333.014 7.053.072 2.695.272.273 2.69.073 7.052.014 8.333 0 8.741 0 12c0 3.259.014 3.668.072 4.948.2 4.358 2.618 6.78 6.98 6.98C8.333 23.986 8.741 24 12 24c3.259 0 3.668-.014 4.948-.072 4.354-.2 6.782-2.618 6.979-6.98.059-1.28.073-1.689.073-4.948 0-3.259-.014-3.667-.072-4.947-.196-4.354-2.617-6.78-6.979-6.98C15.668.014 15.259 0 12 0zm0 5.838a6.162 6.162 0 1 0 0 12.324 6.162 6.162 0 0 0 0-12.324zM12 16a4 4 0 1 1 0-8 4 4 0 0 1 0 8zm6.406-11.845a1.44 1.44 0 1 0 0 2.881 1.44 1.44 0 0 0 0-2.881z"/></svg>
                                        @elseif ($name === 'YouTube')
                                            <svg viewBox="0 0 24 24" aria-hidden="true"><path d="M23.498 6.186a3.016 3.016 0 0 0-2.122-2.136C19.505 3.545 12 3.545 12 3.545s-7.505 0-9.377.505A3.017 3.017 0 0 0 .502 6.186C0 8.07 0 12 0 12s0 3.93.502 5.814a3.016 3.016 0 0 0 2.122 2.136c1.871.505 9.376.505 9.376.505s7.505 0 9.377-.505a3.015 3.015 0 0 0 2.122-2.136C24 15.93 24 12 24 12s0-3.93-.502-5.814zM9.545 15.568V8.432L15.818 12l-6.273 3.568z"/></svg>
                                        @else
                                            <svg viewBox="0 0 24 24" aria-hidden="true"><path d="M18.901 1.153h3.68l-8.04 9.19L24 22.846h-7.406l-5.8-7.584-6.638 7.584H.474l8.6-9.83L0 1.154h7.594l5.243 6.932ZM17.61 20.644h2.039L6.486 3.24H4.298Z"/></svg>
                                        @endif
                                    </a>
                                @endforeach
                            </div>
                            </div>
                        </div>
                    @endif
                </div>
            </div>

        </div>
    </div>

    {{-- Yellow decorative wave --}}
    <svg class="eb-wave" viewBox="0 0 1440 100" preserveAspectRatio="none" aria-hidden="true">
        <path fill="#FFD56B" d="M0,40 C240,95 480,0 720,35 C960,70 1200,10 1440,45 L1440,100 L0,100 Z"/>
        <path fill="#FFB81C" d="M0,65 C240,105 480,25 720,60 C960,95 1200,35 1440,70 L1440,100 L0,100 Z"/>
    </svg>
</section>
