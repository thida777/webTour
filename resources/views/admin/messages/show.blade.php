@extends('layouts.app')

@section('title', 'Message - Eocambo Tours')

@section('content')

    <section class="py-5">
        <div class="container">
            <div class="row justify-content-center">
                <div class="col-lg-8">
                    <a href="/admin/messages" class="btn btn-outline-secondary btn-sm mb-3">&larr; Back to Messages</a>

                    <div class="card shadow-sm">
                        <div class="card-header d-flex justify-content-between align-items-center">
                            <h1 class="h5 mb-0">{{ $contact->subject }}</h1>
                            @if ($contact->is_read)
                                <span class="badge bg-secondary">Read</span>
                            @else
                                <span class="badge bg-danger">Unread</span>
                            @endif
                        </div>

                        <div class="card-body">
                            <p class="mb-1"><strong>From:</strong> {{ $contact->name }}</p>
                            <p class="mb-1"><strong>Email:</strong> {{ $contact->email }}</p>
                            <p class="mb-3"><strong>Received:</strong> {{ $contact->created_at->format('d M Y, H:i') }}</p>

                            <hr>

                            <p class="mb-0">{!! nl2br(e($contact->message)) !!}</p>
                        </div>

                        <div class="card-footer">
                            {{-- Opens the admin's own email program; no reply system is built here. --}}
                            <a href="mailto:{{ $contact->email }}?subject={{ rawurlencode('Re: ' . $contact->subject) }}" class="btn btn-success">Reply by Email</a>
                            <a href="/admin/messages" class="btn btn-outline-secondary">Back to Messages</a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

@endsection
