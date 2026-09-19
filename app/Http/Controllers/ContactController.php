<?php

namespace App\Http\Controllers;

use App\Mail\ContactMessageMail;
use App\Models\Contact;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;

class ContactController extends Controller
{
    /**
     * The email address that receives contact messages.
     */
    private const ADMIN_EMAIL = 'touradmin@eocambo.dev';

    /**
     * Show the contact form.
     */
    public function create()
    {
        return view('contact');
    }

    /**
     * Validate and save a contact message, then email it to the admin.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|max:255',
            'subject' => 'required|string|max:255',
            'message' => 'required|string|max:5000',
        ]);

        // 1. Save the message first.
        $contact = Contact::create($validated + ['is_read' => false]);

        // 2. Only after it is saved, email the admin.
        //    If the email fails, the message is still safely stored in the database.
        try {
            Mail::to(self::ADMIN_EMAIL)->send(new ContactMessageMail($contact));
        } catch (\Throwable $e) {
            Log::error('Contact email failed to send (contact id ' . $contact->id . '): ' . $e->getMessage());
        }

        return redirect('/contact')->with('success', 'Thank you! Your message has been sent. We will get back to you soon.');
    }
}
