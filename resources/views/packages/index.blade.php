@extends('layouts.app')

@section('title', 'Our Tour Packages - Eocambo Tours')

@section('content')

    <section class="py-5">
        <div class="container">
            <h1 class="text-center mb-4">Our Tour Packages</h1>

            @if ($packages->isEmpty())
                <p class="text-center text-muted">No tour packages are available right now. Please check back soon.</p>
            @else
                <div class="row g-4">
                    @foreach ($packages as $package)
                        <div class="col-md-6 col-lg-4">
                            {{-- The title link is "stretched" over the whole card, so the whole card opens the details page.
                                 BOOK NOW is a separate link (position-relative z-2) sitting above it, so links are never nested. --}}
                            <div class="card h-100 shadow-sm package-card">
                                @if ($package->image)
                                    <img src="{{ asset('storage/' . $package->image) }}" alt="{{ $package->name }}" class="card-img-top" style="height: 200px; object-fit: cover;">
                                @endif
                                <div class="card-body d-flex flex-column">
                                    <h5 class="card-title"><a href="/packages/{{ $package->id }}" class="stretched-link text-decoration-none text-reset">{{ $package->name }}</a></h5>
                                    <h6 class="card-subtitle mb-3 text-muted">{{ $package->location }}</h6>
                                    <p class="mb-1"><strong>Duration:</strong> {{ $package->duration }}</p>
                                    <p class="fw-bold text-success">${{ number_format($package->price, 2) }}</p>
                                    <p class="card-text">{{ \Illuminate\Support\Str::limit($package->description, 100) }}</p>
                                    <a href="/booking?package={{ $package->id }}" class="btn btn-success mt-auto position-relative z-2">BOOK NOW</a>
                                </div>
                            </div>
                        </div>
                    @endforeach
                </div>
            @endif
        </div>
    </section>

@endsection
