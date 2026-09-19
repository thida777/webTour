<?php

namespace Tests\Feature;

use App\Mail\BookingRequestMail;
use App\Mail\ContactMessageMail;
use App\Models\Booking;
use App\Models\Contact;
use App\Models\Package;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Tests\TestCase;

/**
 * Contact and Booking notification emails. Mail::fake() means NO real email is ever sent.
 */
class NotificationEmailTest extends TestCase
{
    use RefreshDatabase;

    private const ADMIN = 'touradmin@eocambo.dev';

    private function package(array $override = []): Package
    {
        return Package::create($override + ['name' => 'Angkor Wat Sunrise Tour', 'location' => 'Siem Reap', 'price' => 45,
            'duration' => '1 Day', 'description' => 'Temples', 'status' => true]);
    }

    private function bookingData(Package $package, array $override = []): array
    {
        return $override + [
            'package_id' => $package->id,
            'name' => 'Sok Dara',
            'email' => 'dara@example.com',
            'phone' => '+855 12 345 678',
            'travel_date' => now()->addDays(10)->format('Y-m-d'),
            'guests' => 3,
            'message' => "Pick us up at the hotel.\nThank you!",
        ];
    }

    // ------------------------------------------------------------------ CONTACT

    public function test_contact_form_saves_the_message_and_emails_the_admin(): void
    {
        Mail::fake();

        $this->post('/contact', [
            'name' => 'Mary', 'email' => 'mary@example.com', 'subject' => 'Question about Angkor', 'message' => 'Do you run tours in December?',
        ])->assertRedirect('/contact')->assertSessionHas('success');

        $this->assertDatabaseHas('contacts', ['email' => 'mary@example.com', 'subject' => 'Question about Angkor', 'is_read' => false]);

        Mail::assertSentCount(1);
        Mail::assertSent(ContactMessageMail::class, function (ContactMessageMail $mail) {
            return $mail->hasTo(self::ADMIN)
                && $mail->hasReplyTo('mary@example.com')
                && $mail->hasSubject('New Contact Message: Question about Angkor');
        });
        Mail::assertNotSent(BookingRequestMail::class);
    }

    public function test_contact_email_contains_the_customers_details(): void
    {
        $contact = Contact::create(['name' => 'Mary', 'email' => 'mary@example.com', 'subject' => 'Hello', 'message' => "Line one\n<b>bold</b>", 'is_read' => false]);

        $html = (new ContactMessageMail($contact))->render();

        $this->assertStringContainsString('Mary', $html);
        $this->assertStringContainsString('mary@example.com', $html);
        $this->assertStringContainsString('Line one', $html);
        $this->assertStringContainsString('&lt;b&gt;bold&lt;/b&gt;', $html); // customer text is escaped
    }

    public function test_invalid_contact_form_saves_nothing_and_sends_nothing(): void
    {
        Mail::fake();

        $this->post('/contact', ['name' => '', 'email' => 'not-an-email', 'subject' => '', 'message' => ''])
            ->assertSessionHasErrors(['name', 'email', 'subject', 'message']);

        $this->assertDatabaseCount('contacts', 0);
        Mail::assertNothingSent();
    }

    public function test_contact_message_is_still_saved_if_the_email_fails(): void
    {
        Mail::shouldReceive('to')->once()->andThrow(new \RuntimeException('SMTP is down'));
        Log::shouldReceive('error')->once();

        $this->post('/contact', ['name' => 'Mary', 'email' => 'mary@example.com', 'subject' => 'Hi', 'message' => 'Hello'])
            ->assertRedirect('/contact')->assertSessionHas('success');

        $this->assertDatabaseHas('contacts', ['email' => 'mary@example.com']);
    }

    // ------------------------------------------------------------------ BOOKING

    public function test_booking_form_saves_the_booking_and_emails_the_admin(): void
    {
        Mail::fake();
        $package = $this->package();

        $this->post('/booking', $this->bookingData($package))
            ->assertRedirect('/booking')->assertSessionHas('success');

        $this->assertDatabaseHas('bookings', [
            'package_id' => $package->id, 'package_name' => 'Angkor Wat Sunrise Tour', 'name' => 'Sok Dara',
            'email' => 'dara@example.com', 'phone' => '+855 12 345 678', 'guests' => 3, 'is_read' => false,
        ]);
        $this->assertSame(now()->addDays(10)->format('Y-m-d'), Booking::first()->travel_date->format('Y-m-d'));

        Mail::assertSentCount(1);
        Mail::assertSent(BookingRequestMail::class, function (BookingRequestMail $mail) {
            return $mail->hasTo(self::ADMIN)
                && $mail->hasReplyTo('dara@example.com')
                && $mail->hasSubject('New Booking Request: Angkor Wat Sunrise Tour - Sok Dara');
        });
        Mail::assertNotSent(ContactMessageMail::class);
    }

    public function test_booking_email_contains_all_booking_details(): void
    {
        $package = $this->package();
        $booking = Booking::create($this->bookingData($package, ['package_name' => $package->name, 'message' => "Hi <script>alert(1)</script>\nBye"]));

        $html = (new BookingRequestMail($booking))->render();

        foreach (['Angkor Wat Sunrise Tour', 'Sok Dara', 'dara@example.com', '+855 12 345 678', now()->addDays(10)->format('d M Y'), 'Bye'] as $expected) {
            $this->assertStringContainsString($expected, $html);
        }
        $this->assertMatchesRegularExpression('#Number of guests</p>\s*<p[^>]*>3</p>#', $html);
        $this->assertStringNotContainsString('<script>', $html);   // customer text is escaped
        $this->assertStringContainsString('&lt;script&gt;', $html);
    }

    public function test_booking_works_without_phone_and_message(): void
    {
        Mail::fake();
        $package = $this->package();

        $this->post('/booking', $this->bookingData($package, ['phone' => '', 'message' => '']))->assertSessionHasNoErrors();

        $booking = Booking::first();
        $this->assertNull($booking->phone);
        $this->assertNull($booking->message);
        Mail::assertSent(BookingRequestMail::class);
        $this->assertStringContainsString('Sok Dara', (new BookingRequestMail($booking))->render());
    }

    public function test_invalid_bookings_save_nothing_and_send_nothing(): void
    {
        Mail::fake();
        $package = $this->package();
        $inactive = $this->package(['name' => 'Hidden Tour', 'status' => false]);

        $this->post('/booking', [])->assertSessionHasErrors(['package_id', 'name', 'email', 'travel_date', 'guests']);
        $this->post('/booking', $this->bookingData($package, ['email' => 'nope']))->assertSessionHasErrors('email');
        $this->post('/booking', $this->bookingData($package, ['travel_date' => now()->subDay()->format('Y-m-d')]))->assertSessionHasErrors('travel_date');
        $this->post('/booking', $this->bookingData($package, ['guests' => 0]))->assertSessionHasErrors('guests');
        $this->post('/booking', $this->bookingData($package, ['guests' => 51]))->assertSessionHasErrors('guests');
        $this->post('/booking', $this->bookingData($package, ['package_id' => 9999]))->assertSessionHasErrors('package_id');
        $this->post('/booking', $this->bookingData($inactive))->assertSessionHasErrors('package_id'); // inactive tours cannot be booked

        $this->assertDatabaseCount('bookings', 0);
        Mail::assertNothingSent();
    }

    public function test_booking_is_still_saved_if_the_email_fails(): void
    {
        Mail::shouldReceive('to')->once()->andThrow(new \RuntimeException('SMTP is down'));
        Log::shouldReceive('error')->once();
        $package = $this->package();

        $this->post('/booking', $this->bookingData($package))->assertRedirect('/booking')->assertSessionHas('success');

        $this->assertDatabaseHas('bookings', ['email' => 'dara@example.com', 'package_name' => 'Angkor Wat Sunrise Tour']);
    }

    public function test_booking_form_page_lists_only_active_packages_and_preselects_one(): void
    {
        $active = $this->package();
        $second = $this->package(['name' => 'Phnom Penh City Tour']);
        $this->package(['name' => 'Hidden Tour', 'status' => false]);

        $this->get('/booking')->assertOk()
            ->assertSee('Book a Tour')->assertSee('Angkor Wat Sunrise Tour')->assertSee('Phnom Penh City Tour')
            ->assertDontSee('Hidden Tour');

        $this->get('/booking?package=' . $second->id)->assertOk()
            ->assertSee('value="' . $second->id . '" selected', false)
            ->assertDontSee('value="' . $active->id . '" selected', false);
    }

    public function test_booking_page_without_packages_shows_a_friendly_message(): void
    {
        $this->get('/booking')->assertOk()->assertSee('No tour packages are available')->assertDontSee('<form action="/booking"', false);
    }

    public function test_deleting_a_package_keeps_its_bookings_readable(): void
    {
        Mail::fake();
        $package = $this->package();
        $this->post('/booking', $this->bookingData($package));

        $package->delete();

        $booking = Booking::first();
        $this->assertNotNull($booking);
        $this->assertNull($booking->package_id);
        $this->assertSame('Angkor Wat Sunrise Tour', $booking->package_name);

        $this->actingAs(User::factory()->create())->get('/admin/bookings')->assertOk()->assertSee('Angkor Wat Sunrise Tour');
    }

    // ------------------------------------------------------------ ADMIN BOOKINGS

    public function test_guests_cannot_see_bookings(): void
    {
        $booking = Booking::create($this->bookingData($this->package(), ['package_name' => 'X']));

        $this->get('/admin/bookings')->assertRedirect('/admin/login');
        $this->get("/admin/bookings/{$booking->id}")->assertRedirect('/admin/login');
        $this->post("/admin/bookings/{$booking->id}/read")->assertRedirect('/admin/login');
        $this->assertFalse($booking->fresh()->is_read);
    }

    public function test_admin_can_list_open_and_mark_bookings_read(): void
    {
        $package = $this->package();
        $first = Booking::create($this->bookingData($package, ['package_name' => $package->name, 'name' => 'First Customer']));
        $second = Booking::create($this->bookingData($package, ['package_name' => $package->name, 'name' => 'Second Customer']));
        $this->actingAs(User::factory()->create());

        $this->get('/admin/bookings')->assertOk()
            ->assertSee('Total bookings: <strong>2</strong>', false)->assertSee('Unread: <strong>2</strong>', false)
            ->assertSee('First Customer')->assertSee('Second Customer')->assertSee('table-warning', false);

        // Opening a booking shows all its details and marks it read.
        $this->get("/admin/bookings/{$first->id}")->assertOk()
            ->assertSee('Angkor Wat Sunrise Tour')->assertSee('First Customer')->assertSee('dara@example.com')
            ->assertSee('+855 12 345 678')->assertSee('Pick us up at the hotel.')->assertSee('Guests:');
        $this->assertTrue($first->fresh()->is_read);

        // "Mark as Read" from the list.
        $this->post("/admin/bookings/{$second->id}/read")->assertRedirect('/admin/bookings')->assertSessionHas('success');
        $this->assertTrue($second->fresh()->is_read);
        $this->get('/admin/bookings')->assertSee('Unread: <strong>0</strong>', false);
    }

    public function test_admin_bookings_page_is_in_the_navbar_and_message_inbox_is_unchanged(): void
    {
        $this->actingAs(User::factory()->create());
        Contact::create(['name' => 'Mary', 'email' => 'mary@example.com', 'subject' => 'Hello there', 'message' => 'Hi', 'is_read' => false]);

        $html = $this->get('/admin/bookings')->getContent();
        $nav = substr($html, strpos($html, '<nav'), strpos($html, '</nav>') - strpos($html, '<nav'));
        $this->assertMatchesRegularExpression('#nav-link active" href="/admin/bookings"[^>]*>Bookings</a>#', $nav);
        foreach (['Packages', 'Messages', 'Bookings', 'Banner Settings', 'Logout'] as $item) {
            $this->assertStringContainsString($item, $nav);
        }

        // Admin -> Messages still works exactly as before.
        $this->get('/admin/messages')->assertOk()->assertSee('Hello there')->assertSee('Mary');
    }

    public function test_public_footer_links_to_the_booking_form(): void
    {
        $this->get('/')->assertOk()->assertSee('<a href="/booking">Book a Tour</a>', false);
    }
}
