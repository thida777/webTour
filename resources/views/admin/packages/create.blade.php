@extends('layouts.app')

@section('title', 'Add Package - Eocambo Tours')

@section('content')

    <section class="py-5">
        <div class="container">
            <div class="row justify-content-center">
                <div class="col-lg-8">
                    <h1 class="h3 mb-4">Add New Package</h1>

                    <form action="/admin/packages" method="POST" enctype="multipart/form-data" class="form-panel" novalidate>
                        @csrf

                        @include('admin.packages._form')

                        <button type="submit" class="btn btn-success">Create Package</button>
                        <a href="/admin/packages" class="btn btn-outline-secondary">Cancel</a>
                    </form>
                </div>
            </div>
        </div>
    </section>

@endsection
