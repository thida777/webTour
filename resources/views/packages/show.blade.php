@extends('layouts.app')

@section('title', $package->name . ' - Eocambo Tours')

@section('content')

    <section class="py-5">
        <div class="container">
            <a href="/packages" class="btn btn-outline-secondary btn-sm mb-4">&larr; Back to Packages</a>

            <div class="row g-4">
                @if ($package->image)
                    <div class="col-md-6">
                        <img src="{{ asset('storage/' . $package->image) }}" alt="{{ $package->name }}" class="img-fluid rounded shadow-sm">
                    </div>
                @endif

                <div class="{{ $package->image ? 'col-md-6' : 'col-12' }}">
                    <h1 class="mb-2">{{ $package->name }}</h1>
                    <h5 class="text-muted mb-3">{{ $package->location }}</h5>

                    <p class="mb-1"><strong>Duration:</strong> {{ $package->duration }}</p>
                    <p class="fs-4 fw-bold text-success">${{ number_format($package->price, 2) }}</p>

                    <p>{!! nl2br(e($package->description)) !!}</p>

                    <a href="/booking?package={{ $package->id }}" class="btn btn-success btn-lg mt-2">BOOK NOW</a>
                </div>
            </div>
        </div>
    </section>

@endsection
