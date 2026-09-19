<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>New Contact Message</title>
</head>
<body style="margin: 0; padding: 20px; background-color: #f4f4f4; font-family: Arial, Helvetica, sans-serif; color: #333333;">

    <div style="max-width: 600px; margin: 0 auto; background-color: #ffffff; border-radius: 6px; overflow: hidden;">

        <div style="background-color: #198754; color: #ffffff; padding: 20px;">
            <h2 style="margin: 0;">Eocambo Tours</h2>
            <p style="margin: 5px 0 0 0;">New contact message from the website</p>
        </div>

        <div style="padding: 20px;">
            <p style="margin: 0 0 5px 0; color: #777777; font-size: 13px;">Name</p>
            <p style="margin: 0 0 15px 0; font-size: 16px;">{{ $contact->name }}</p>

            <p style="margin: 0 0 5px 0; color: #777777; font-size: 13px;">Email</p>
            <p style="margin: 0 0 15px 0; font-size: 16px;">{{ $contact->email }}</p>

            <p style="margin: 0 0 5px 0; color: #777777; font-size: 13px;">Subject</p>
            <p style="margin: 0 0 15px 0; font-size: 16px;">{{ $contact->subject }}</p>

            <p style="margin: 0 0 5px 0; color: #777777; font-size: 13px;">Message</p>
            <div style="padding: 12px; background-color: #f8f9fa; border-left: 4px solid #198754; font-size: 15px; line-height: 1.5;">
                {!! nl2br(e($contact->message)) !!}
            </div>
        </div>

        <div style="padding: 15px 20px; background-color: #f8f9fa; color: #777777; font-size: 12px;">
            Received on {{ $contact->created_at->format('d M Y, H:i') }}. You can reply directly to this email to answer the customer.
        </div>

    </div>

</body>
</html>
