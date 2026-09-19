@extends('layouts.app')

@section('title', 'Edit Package - Eocambo Tours')

@section('content')

    <section class="py-5">
        <div class="container">
            <div class="row justify-content-center">
                <div class="col-lg-8">
                    <h1 class="h3 mb-4">Edit Package</h1>

                    <form action="/admin/packages/{{ $package->id }}" method="POST" enctype="multipart/form-data" class="form-panel" novalidate>
                        @csrf
                        @method('PUT')

                        @include('admin.packages._form')

                        <button type="submit" class="btn btn-success">Update Package</button>
                        <a href="/admin/packages" class="btn btn-outline-secondary">Cancel</a>
                    </form>
                </div>
            </div>
        </div>
    </section>

@endsection
