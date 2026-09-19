<?php

namespace App\Http\Controllers;

use App\Models\Contact;

class AdminMessageController extends Controller
{
    /**
     * List all customer messages, newest first.
     */
    public function index()
    {
        $contacts = Contact::latest()->get();

        return view('admin.messages.index', compact('contacts'));
    }

    /**
     * Show one message. Opening an unread message marks it as read.
     */
    public function show(Contact $contact)
    {
        if (! $contact->is_read) {
            $contact->update(['is_read' => true]);
        }

        return view('admin.messages.show', compact('contact'));
    }

    /**
     * Mark a message as read without opening it.
     */
    public function markRead(Contact $contact)
    {
        $contact->update(['is_read' => true]);

        return redirect('/admin/messages')->with('success', 'Message marked as read.');
    }
}
