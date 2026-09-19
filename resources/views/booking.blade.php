@extends('layouts.app')

@section('title', 'Book a Tour - Eocambo Tours')

@section('content')

    <section class="py-5">
        <div class="container">
            <div class="row justify-content-center">
                <div class="col-md-8 col-lg-6">
                    <h1 class="text-center mb-2">Book a Tour</h1>
                    <p class="text-center text-muted mb-4">Choose your tour and travel date. We will contact you to confirm your booking.</p>

                    @if (session('success'))
                        <div class="alert alert-success">{{ session('success') }}</div>
                    @endif

                    @if ($packages->isEmpty())
                        <p class="text-center text-muted">No tour packages are available for booking right now. Please check back soon.</p>
                    @else
                        <form action="/booking" method="POST" class="form-panel" novalidate>
                            @csrf

                            {{-- The package that is selected right now (from BOOK NOW, or from the form after a validation error). --}}
                            @php
                                $chosenId = (string) old('package_id', $selectedPackage);
                                $chosen = $packages->first(fn ($p) => (string) $p->id === $chosenId);
                            @endphp

                            <div class="mb-3">
                                {{-- Image of the selected package. It is shown by the server and updated by the script below when the dropdown changes. --}}
                                <div id="package-preview" class="mb-3 {{ $chosen && $chosen->image ? '' : 'd-none' }}">
                                    <img id="package-preview-img" src="{{ $chosen && $chosen->image ? asset('storage/' . $chosen->image) : '' }}" alt="{{ $chosen->name ?? '' }}" class="img-fluid rounded w-100" style="height: 220px; object-fit: cover;">
                                </div>

                                <label for="package_id" class="form-label">Tour package</label>
                                <select id="package_id" name="package_id" class="form-select @error('package_id') is-invalid @enderror">
                                    <option value="">Choose a tour...</option>
                                    @foreach ($packages as $package)
                                        <option value="{{ $package->id }}" {{ $chosen && $chosen->id === $package->id ? 'selected' : '' }} data-name="{{ $package->name }}" data-image="{{ $package->image ? asset('storage/' . $package->image) : '' }}">
                                            {{ $package->name }} ({{ $package->duration }}, ${{ number_format($package->price, 2) }})
                                        </option>
                                    @endforeach
                                </select>
                                @error('package_id')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="row">
                                <div class="col-sm-7 mb-3">
                                    <label for="travel_date" class="form-label">Travel date</label>
                                    <input type="date" id="travel_date" name="travel_date" min="{{ date('Y-m-d') }}" value="{{ old('travel_date') }}" class="form-control @error('travel_date') is-invalid @enderror">
                                    @error('travel_date')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>

                                <div class="col-sm-5 mb-3">
                                    <label for="guests" class="form-label">Guests</label>
                                    <input type="number" id="guests" name="guests" min="1" max="50" value="{{ old('guests', 1) }}" class="form-control @error('guests') is-invalid @enderror">
                                    @error('guests')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>

                            <div class="mb-3">
                                <label for="name" class="form-label">Your name</label>
                                <input type="text" id="name" name="name" value="{{ old('name') }}" class="form-control @error('name') is-invalid @enderror">
                                @error('name')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="mb-3">
                                <label for="email" class="form-label">Email</label>
                                <input type="email" id="email" name="email" value="{{ old('email') }}" class="form-control @error('email') is-invalid @enderror">
                                @error('email')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="mb-3">
                                <label for="phone" class="form-label">Phone (optional)</label>
                                <input type="text" id="phone" name="phone" value="{{ old('phone') }}" class="form-control @error('phone') is-invalid @enderror">
                                @error('phone')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="mb-3">
                                <label for="message" class="form-label">Message (optional)</label>
                                <textarea id="message" name="message" rows="4" placeholder="Pick-up place, special requests..." class="form-control @error('message') is-invalid @enderror">{{ old('message') }}</textarea>
                                @error('message')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <button type="submit" class="btn btn-success w-100">Send Booking Request</button>
                        </form>

                        {{-- Show the image of whichever package is chosen in the dropdown. --}}
                        <script>
                            (function () {
                                var select = document.getElementById('package_id');
                                var box = document.getElementById('package-preview');
                                var img = document.getElementById('package-preview-img');

                                select.addEventListener('change', function () {
                                    var option = select.options[select.selectedIndex];
                                    var url = option.getAttribute('data-image');

                                    if (url) {
                                        img.src = url;
                                        img.alt = option.getAttribute('data-name') || '';
                                        box.classList.remove('d-none');
                                    } else {
                                        box.classList.add('d-none');   // no package chosen, or this package has no image
                                    }
                                });
                            })();
                        </script>
                    @endif
                </div>
            </div>
        </div>
    </section>

@endsection
