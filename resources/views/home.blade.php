@extends('layouts.app')

@section('title', 'Eocambo Tours - Discover Cambodia')

@section('content')

    {{-- Editable banner (Admin > Banner Settings). If it is hidden, the old hero section is shown instead. --}}
    @if ($banner && $banner->is_active)
        @include('partials.home-banner')
    @else
        {{-- Hero section --}}
        <section class="bg-success text-white text-center py-5">
            <div class="container py-5">
                <h1 class="display-4 fw-bold">Eocambo Tours</h1>
                <p class="lead mb-4">Welcome to the Kingdom of Wonder. Explore ancient temples, beautiful beaches and friendly local life with us.</p>
                <a href="/packages" class="btn btn-light btn-lg">View Packages</a>
            </div>
        </section>
    @endif

    {{-- Introduction section --}}
    <section class="py-5">
        <div class="container text-center">
            <h2 class="mb-3">About Eocambo Tours</h2>
            <p class="lead text-muted mx-auto" style="max-width: 700px;">
                Eocambo Tours provides tour experiences in Cambodia. From the temples of Angkor Wat
                to the streets of Phnom Penh and the coast of Kampot, we help you enjoy the best of
                our country with comfort and care.
            </p>
        </div>
    </section>

    {{-- Featured tours section (3 newest active packages from the database) --}}
    <section class="bg-light py-5">
        <div class="container">
            <h2 class="text-center mb-4">Featured Tours</h2>

            <div class="row g-4">
                @foreach ($featuredPackages as $package)
                    <div class="col-md-4">
                        <a href="/packages/{{ $package->id }}" class="card h-100 shadow-sm text-decoration-none text-reset">
                            @if ($package->image)
                                <img src="{{ asset('storage/' . $package->image) }}" alt="{{ $package->name }}" class="card-img-top" style="height: 200px; object-fit: cover;">
                            @endif
                            <div class="card-body d-flex flex-column">
                                <h5 class="card-title">{{ $package->name }}</h5>
                                <h6 class="card-subtitle mb-3 text-muted">{{ $package->location }}</h6>
                                <p class="mb-1"><strong>Duration:</strong> {{ $package->duration }}</p>
                                <p class="fw-bold text-success">${{ number_format($package->price, 2) }}</p>
                                <p class="card-text">{{ \Illuminate\Support\Str::limit($package->description, 100) }}</p>
                            </div>
                        </a>
                    </div>
                @endforeach
            </div>
        </div>
    </section>

    {{-- Why choose us section --}}
    <section class="py-5">
        <div class="container">
            <h2 class="text-center mb-4">Why Choose Us</h2>

            <div class="row g-4 text-center">
                <div class="col-md-4">
                    <div class="bg-white rounded-4 shadow-sm p-4 h-100">
                        <h5>Local Experience</h5>
                        <p class="text-muted mb-0">Our guides are Cambodians who know the history, culture and hidden places of the country.</p>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="bg-white rounded-4 shadow-sm p-4 h-100">
                        <h5>Affordable Tours</h5>
                        <p class="text-muted mb-0">Good prices for every traveler, with no hidden costs.</p>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="bg-white rounded-4 shadow-sm p-4 h-100">
                        <h5>Friendly Service</h5>
                        <p class="text-muted mb-0">We welcome you like family and are happy to help before and during your trip.</p>
                    </div>
                </div>
            </div>
        </div>
    </section>

@endsection
