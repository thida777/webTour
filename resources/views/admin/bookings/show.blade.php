@extends('layouts.app')

@section('title', 'Booking - Eocambo Tours')

@section('content')

    <section class="py-5">
        <div class="container">
            <div class="row justify-content-center">
                <div class="col-lg-8">
                    <a href="/admin/bookings" class="btn btn-outline-secondary btn-sm mb-3">&larr; Back to Bookings</a>

                    <div class="card shadow-sm">
                        <div class="card-header d-flex justify-content-between align-items-center">
                            <h1 class="h5 mb-0">{{ $booking->package_name }}</h1>
                            @if ($booking->is_read)
                                <span class="badge bg-secondary">Read</span>
                            @else
                                <span class="badge bg-danger">New</span>
                            @endif
                        </div>

                        <div class="card-body">
                            <p class="mb-1"><strong>Travel date:</strong> {{ $booking->travel_date->format('d M Y') }}</p>
                            <p class="mb-1"><strong>Guests:</strong> {{ $booking->guests }}</p>
                            <p class="mb-3"><strong>Received:</strong> {{ $booking->created_at->format('d M Y, H:i') }}</p>

                            <hr>

                            <p class="mb-1"><strong>Name:</strong> {{ $booking->name }}</p>
                            <p class="mb-1"><strong>Email:</strong> {{ $booking->email }}</p>
                            <p class="mb-3"><strong>Phone:</strong> {{ $booking->phone ?: '-' }}</p>

                            @if ($booking->message)
                                <hr>
                                <p class="mb-0">{!! nl2br(e($booking->message)) !!}</p>
                            @endif
                        </div>

                        <div class="card-footer">
                            {{-- Opens the admin's own email program; no reply system is built here. --}}
                            <a href="mailto:{{ $booking->email }}?subject={{ rawurlencode('Your booking: ' . $booking->package_name) }}" class="btn btn-success">Reply by Email</a>
                            <a href="/admin/bookings" class="btn btn-outline-secondary">Back to Bookings</a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

@endsection
