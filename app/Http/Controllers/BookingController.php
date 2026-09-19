<?php

namespace App\Http\Controllers;

use App\Mail\BookingRequestMail;
use App\Models\Booking;
use App\Models\Package;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Illuminate\Validation\Rule;

class BookingController extends Controller
{
    /**
     * The email address that receives booking requests (same address as the contact form).
     */
    private const ADMIN_EMAIL = 'touradmin@eocambo.dev';

    /**
     * Show the booking form. /booking?package=3 pre-selects that package.
     */
    public function create(Request $request)
    {
        $packages = Package::where('status', true)->latest()->get();

        return view('booking', [
            'packages' => $packages,
            'selectedPackage' => $request->query('package'),
        ]);
    }

    /**
     * Validate and save a booking request, then email it to the admin.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            // Only active packages can be booked.
            'package_id' => ['required', Rule::exists('packages', 'id')->where('status', true)],
            'name' => 'required|string|max:255',
            'email' => 'required|email|max:255',
            'phone' => 'nullable|string|max:50',
            'travel_date' => 'required|date|after_or_equal:today',
            'guests' => 'required|integer|min:1|max:50',
            'message' => 'nullable|string|max:5000',
        ]);

        // 1. Save the booking first (with the package name, so it stays readable if the package is deleted later).
        $package = Package::findOrFail($validated['package_id']);
        $booking = Booking::create($validated + ['package_name' => $package->name, 'is_read' => false]);

        // 2. Only after it is saved, email the admin.
        //    If the email fails, the booking is still safely stored in the database.
        try {
            Mail::to(self::ADMIN_EMAIL)->send(new BookingRequestMail($booking));
        } catch (\Throwable $e) {
            Log::error('Booking email failed to send (booking id ' . $booking->id . '): ' . $e->getMessage());
        }

        return redirect('/booking')->with('success', 'Thank you! Your booking request has been sent. We will contact you soon to confirm it.');
    }
}
