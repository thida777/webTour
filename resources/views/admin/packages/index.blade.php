@extends('layouts.app')

@section('title', 'Manage Packages - Eocambo Tours')

@section('content')

    <section class="py-5">
        <div class="container">
            <div class="d-flex flex-wrap justify-content-between align-items-center mb-4 gap-2">
                <div>
                    <a href="/admin/dashboard" class="btn btn-outline-secondary btn-sm mb-2">&larr; Dashboard</a>
                    <h1 class="h3 mb-0">Manage Packages</h1>
                </div>
                <a href="/admin/packages/create" class="btn btn-success">Add New Package</a>
            </div>

            @if (session('success'))
                <div class="alert alert-success">{{ session('success') }}</div>
            @endif

            @if ($packages->isEmpty())
                <p class="text-center text-muted py-4">No tour packages found.</p>
            @else
                <div class="table-responsive">
                    <table class="table table-bordered align-middle">
                        <thead class="table-light">
                            <tr>
                                <th>Image</th>
                                <th>Name</th>
                                <th>Location</th>
                                <th>Price</th>
                                <th>Duration</th>
                                <th>Status</th>
                                <th style="width: 160px;">Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($packages as $package)
                                <tr>
                                    <td>
                                        @if ($package->image)
                                            <img src="{{ asset('storage/' . $package->image) }}" alt="{{ $package->name }}" class="img-thumbnail" style="width: 80px;">
                                        @else
                                            <span class="text-muted">No image</span>
                                        @endif
                                    </td>
                                    <td>{{ $package->name }}</td>
                                    <td>{{ $package->location }}</td>
                                    <td>${{ number_format($package->price, 2) }}</td>
                                    <td>{{ $package->duration }}</td>
                                    <td>
                                        @if ($package->status)
                                            <span class="badge bg-success">Active</span>
                                        @else
                                            <span class="badge bg-secondary">Inactive</span>
                                        @endif
                                    </td>
                                    <td>
                                        <a href="/admin/packages/{{ $package->id }}/edit" class="btn btn-sm btn-outline-primary">Edit</a>

                                        <form action="/admin/packages/{{ $package->id }}" method="POST" class="d-inline" onsubmit="return confirm('Delete this package?');">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="btn btn-sm btn-outline-danger">Delete</button>
                                        </form>
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
