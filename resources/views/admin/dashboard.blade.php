@extends('layouts.app')

@section('title', 'Admin Dashboard - Eocambo Tours')

@section('content')

    <section class="py-5">
        <div class="container">
            <h1 class="h3 mb-4">Admin Dashboard</h1>

            <div class="row g-4">
                <div class="col-md-4">
                    <div class="card text-center shadow-sm">
                        <div class="card-body">
                            <h6 class="text-muted">Total Packages</h6>
                            <p class="display-5 fw-bold mb-0" id="total-packages">{{ $totalPackages }}</p>
                        </div>
                    </div>
                </div>

                <div class="col-md-4">
                    <div class="card text-center shadow-sm">
                        <div class="card-body">
                            <h6 class="text-muted">Total Messages</h6>
                            <p class="display-5 fw-bold mb-0" id="total-messages">{{ $totalMessages }}</p>
                        </div>
                    </div>
                </div>

                <div class="col-md-4">
                    <div class="card text-center shadow-sm">
                        <div class="card-body">
                            <h6 class="text-muted">Unread Messages</h6>
                            <p class="display-5 fw-bold text-danger mb-0" id="unread-messages">{{ $unreadMessages }}</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

@endsection
