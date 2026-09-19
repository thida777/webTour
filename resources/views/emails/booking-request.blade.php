<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>New Booking Request</title>
</head>
<body style="margin: 0; padding: 20px; background-color: #f4f4f4; font-family: Arial, Helvetica, sans-serif; color: #333333;">

    <div style="max-width: 600px; margin: 0 auto; background-color: #ffffff; border-radius: 6px; overflow: hidden;">

        <div style="background-color: #198754; color: #ffffff; padding: 20px;">
            <h2 style="margin: 0;">Eocambo Tours</h2>
            <p style="margin: 5px 0 0 0;">New booking request from the website</p>
        </div>

        <div style="padding: 20px;">
            <p style="margin: 0 0 5px 0; color: #777777; font-size: 13px;">Tour package</p>
            <p style="margin: 0 0 15px 0; font-size: 18px; font-weight: bold;">{{ $booking->package_name }}</p>

            <p style="margin: 0 0 5px 0; color: #777777; font-size: 13px;">Travel date</p>
            <p style="margin: 0 0 15px 0; font-size: 16px;">{{ $booking->travel_date->format('d M Y') }}</p>

            <p style="margin: 0 0 5px 0; color: #777777; font-size: 13px;">Number of guests</p>
            <p style="margin: 0 0 15px 0; font-size: 16px;">{{ $booking->guests }}</p>

            <p style="margin: 0 0 5px 0; color: #777777; font-size: 13px;">Customer name</p>
            <p style="margin: 0 0 15px 0; font-size: 16px;">{{ $booking->name }}</p>

            <p style="margin: 0 0 5px 0; color: #777777; font-size: 13px;">Email</p>
            <p style="margin: 0 0 15px 0; font-size: 16px;">{{ $booking->email }}</p>

            <p style="margin: 0 0 5px 0; color: #777777; font-size: 13px;">Phone</p>
            <p style="margin: 0 0 15px 0; font-size: 16px;">{{ $booking->phone ?: '-' }}</p>

            @if ($booking->message)
                <p style="margin: 0 0 5px 0; color: #777777; font-size: 13px;">Message</p>
                <div style="padding: 12px; background-color: #f8f9fa; border-left: 4px solid #198754; font-size: 15px; line-height: 1.5;">
                    {!! nl2br(e($booking->message)) !!}
                </div>
            @endif
        </div>

        <div style="padding: 15px 20px; background-color: #f8f9fa; color: #777777; font-size: 12px;">
            Received on {{ $booking->created_at->format('d M Y, H:i') }}. You can reply directly to this email to answer the customer.
        </div>

    </div>

</body>
</html>
