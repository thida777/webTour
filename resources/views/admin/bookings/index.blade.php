@extends('layouts.app')

@section('title', 'Bookings - Eocambo Tours')

@section('content')

    <section class="py-5">
        <div class="container">
            <div class="mb-4">
                <h1 class="h3 mb-1">Bookings</h1>
                <p class="text-muted mb-0">
                    Total bookings: <strong>{{ $bookings->count() }}</strong>
                    &middot; Unread: <strong>{{ $bookings->where('is_read', false)->count() }}</strong>
                </p>
            </div>

            @if (session('success'))
                <div class="alert alert-success">{{ session('success') }}</div>
            @endif

            @if ($bookings->isEmpty())
                <p class="text-center text-muted py-4">No bookings found.</p>
            @else
                <div class="table-responsive">
                    <table class="table table-bordered align-middle">
                        <thead class="table-light">
                            <tr>
                                <th>Status</th>
                                <th>Package</th>
                                <th>Customer</th>
                                <th>Travel date</th>
                                <th>Guests</th>
                                <th>Received</th>
                                <th style="width: 190px;">Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($bookings as $booking)
                                {{-- Unread rows: highlighted and bold. Read rows: normal. --}}
                                <tr class="{{ $booking->is_read ? '' : 'table-warning fw-bold' }}">
                                    <td>
                                        @if ($booking->is_read)
                                            <span class="badge bg-secondary">Read</span>
                                        @else
                                            <span class="badge bg-danger">New</span>
                                        @endif
                                    </td>
                                    <td>{{ $booking->package_name }}</td>
                                    <td>
                                        {{ $booking->name }}<br>
                                        <small class="text-muted">{{ $booking->email }}</small>
                                    </td>
                                    <td>{{ $booking->travel_date->format('d M Y') }}</td>
                                    <td>{{ $booking->guests }}</td>
                                    <td>{{ $booking->created_at->format('d M Y, H:i') }}</td>
                                    <td>
                                        <a href="/admin/bookings/{{ $booking->id }}" class="btn btn-sm btn-outline-primary">Open</a>

                                        @unless ($booking->is_read)
                                            <form action="/admin/bookings/{{ $booking->id }}/read" method="POST" class="d-inline">
                                                @csrf
                                                <button type="submit" class="btn btn-sm btn-outline-success">Mark as Read</button>
                                            </form>
                                        @endunless
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @endif
        </div>
    </section>

@endsection
