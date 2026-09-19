<?php

namespace App\Http\Controllers;

use App\Models\Booking;

class AdminBookingController extends Controller
{
    /**
     * List all booking requests, newest first.
     */
    public function index()
    {
        $bookings = Booking::latest()->get();

        return view('admin.bookings.index', compact('bookings'));
    }

    /**
     * Show one booking. Opening an unread booking marks it as read.
     */
    public function show(Booking $booking)
    {
        if (! $booking->is_read) {
            $booking->update(['is_read' => true]);
        }

        return view('admin.bookings.show', compact('booking'));
    }

    /**
     * Mark a booking as read without opening it.
     */
    public function markRead(Booking $booking)
    {
        $booking->update(['is_read' => true]);

        return redirect('/admin/bookings')->with('success', 'Booking marked as read.');
    }
}
