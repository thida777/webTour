@extends('layouts.app')

@section('title', 'Messages - Eocambo Tours')

@section('content')

    <section class="py-5">
        <div class="container">
            <div class="mb-4">
                <a href="/admin/dashboard" class="btn btn-outline-secondary btn-sm mb-2">&larr; Dashboard</a>
                <h1 class="h3 mb-1">Messages</h1>
                <p class="text-muted mb-0">
                    Total messages: <strong>{{ $contacts->count() }}</strong>
                    &middot; Unread: <strong>{{ $contacts->where('is_read', false)->count() }}</strong>
                </p>
            </div>

            @if (session('success'))
                <div class="alert alert-success">{{ session('success') }}</div>
            @endif

            @if ($contacts->isEmpty())
                <p class="text-center text-muted py-4">No messages found.</p>
            @else
                <div class="table-responsive">
                    <table class="table table-bordered align-middle">
                        <thead class="table-light">
                            <tr>
                                <th>Status</th>
                                <th>Name</th>
                                <th>Email</th>
                                <th>Subject</th>
                                <th>Message</th>
                                <th>Date</th>
                                <th style="width: 190px;">Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($contacts as $contact)
                                {{-- Unread rows: highlighted and bold. Read rows: normal. --}}
                                <tr class="{{ $contact->is_read ? '' : 'table-warning fw-bold' }}">
                                    <td>
                                        @if ($contact->is_read)
                                            <span class="badge bg-secondary">Read</span>
                                        @else
                                            <span class="badge bg-danger">Unread</span>
                                        @endif
                                    </td>
                                    <td>{{ $contact->name }}</td>
                                    <td>{{ $contact->email }}</td>
                                    <td>{{ $contact->subject }}</td>
                                    <td>{{ \Illuminate\Support\Str::limit($contact->message, 60) }}</td>
                                    <td>{{ $contact->created_at->format('d M Y, H:i') }}</td>
                                    <td>
                                        <a href="/admin/messages/{{ $contact->id }}" class="btn btn-sm btn-outline-primary">Open</a>

                                        @unless ($contact->is_read)
                                            <form action="/admin/messages/{{ $contact->id }}/read" method="POST" class="d-inline">
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
