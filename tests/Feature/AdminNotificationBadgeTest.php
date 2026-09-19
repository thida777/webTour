<?php

namespace Tests\Feature;

use App\Models\Booking;
use App\Models\Contact;
use App\Models\Package;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;
use Tests\TestCase;

/**
 * Red unread-count badges next to Messages and Bookings in the admin navbar.
 */
class AdminNotificationBadgeTest extends TestCase
{
    use RefreshDatabase;

    private function contact(bool $read = false, string $name = 'Mary'): Contact
    {
        return Contact::create(['name' => $name, 'email' => 'mary@example.com', 'subject' => 'Hello', 'message' => 'Hi there', 'is_read' => $read]);
    }

    private function booking(bool $read = false): Booking
    {
        return Booking::create(['package_id' => null, 'package_name' => 'Angkor Wat Sunrise Tour', 'name' => 'Sok Dara', 'email' => 'dara@example.com',
            'phone' => null, 'travel_date' => now()->addDays(10)->format('Y-m-d'), 'guests' => 2, 'message' => null, 'is_read' => $read]);
    }

    private function nav(string $url = '/admin/dashboard'): string
    {
        $html = $this->get($url)->assertOk()->getContent();
        $start = strpos($html, '<nav');

        return substr($html, $start, strpos($html, '</nav>', $start) - $start);
    }

    /** The badge number next to a nav link ("Messages" / "Bookings"), or null when there is no badge. */
    private function badge(string $nav, string $link): ?string
    {
        preg_match('#href="/admin/' . strtolower($link) . '"[^>]*>' . $link . '(?:<span class="nav-badge">([^<]*)<span class="visually-hidden"> unread</span></span>)?</a>#', $nav, $m);
        $this->assertNotEmpty($m, "$link link not found in the navbar");

        return $m[1] ?? null;
    }

    private function admin(): void
    {
        $this->actingAs(User::factory()->create());
    }

    public function test_no_badges_when_nothing_is_unread(): void
    {
        $this->admin();
        $this->contact(read: true);
        $this->booking(read: true);

        $nav = $this->nav();

        $this->assertNull($this->badge($nav, 'Messages'));
        $this->assertNull($this->badge($nav, 'Bookings'));
        $this->assertStringNotContainsString('<span class="nav-badge">', $nav);
        $this->assertStringContainsString('>Messages</a>', $nav);   // plain links, exactly as before
        $this->assertStringContainsString('>Bookings</a>', $nav);
    }

    public function test_no_badges_at_all_on_an_empty_system(): void
    {
        $this->admin();

        $nav = $this->nav();

        $this->assertNull($this->badge($nav, 'Messages'));
        $this->assertNull($this->badge($nav, 'Bookings'));
    }

    public function test_badge_shows_the_exact_unread_number_and_ignores_read_items(): void
    {
        $this->admin();
        foreach (range(1, 5) as $i) { $this->contact(); }
        foreach (range(1, 3) as $i) { $this->contact(read: true); }          // read ones are not counted
        foreach (range(1, 12) as $i) { $this->booking(); }
        foreach (range(1, 4) as $i) { $this->booking(read: true); }

        $nav = $this->nav();

        $this->assertSame('5', $this->badge($nav, 'Messages'));
        $this->assertSame('12', $this->badge($nav, 'Bookings'));
    }

    public function test_messages_and_bookings_badges_are_independent(): void
    {
        $this->admin();
        $this->contact();
        $this->contact();

        $nav = $this->nav();
        $this->assertSame('2', $this->badge($nav, 'Messages'));
        $this->assertNull($this->badge($nav, 'Bookings'));

        $this->booking();
        $nav = $this->nav();
        $this->assertSame('2', $this->badge($nav, 'Messages'));
        $this->assertSame('1', $this->badge($nav, 'Bookings'));
    }

    public function test_very_large_counts_are_shown_as_99_plus(): void
    {
        $this->admin();
        foreach (range(1, 100) as $i) { $this->contact(); }
        foreach (range(1, 99) as $i) { $this->booking(); }

        $nav = $this->nav();

        $this->assertSame('99+', $this->badge($nav, 'Messages'));
        $this->assertSame('99', $this->badge($nav, 'Bookings'));
    }

    public function test_marking_a_message_as_read_lowers_the_messages_badge_until_it_disappears(): void
    {
        $this->admin();
        $first = $this->contact(name: 'First');
        $second = $this->contact(name: 'Second');
        $this->assertSame('2', $this->badge($this->nav('/admin/messages'), 'Messages'));

        // "Mark as Read" button on the list
        $this->post("/admin/messages/{$first->id}/read")->assertRedirect('/admin/messages');
        $this->assertSame('1', $this->badge($this->nav('/admin/messages'), 'Messages'));

        // Opening the last unread message also marks it read
        $this->get("/admin/messages/{$second->id}")->assertOk();
        $this->assertNull($this->badge($this->nav('/admin/messages'), 'Messages'));
    }

    public function test_marking_a_booking_as_read_lowers_the_bookings_badge_until_it_disappears(): void
    {
        $this->admin();
        $first = $this->booking();
        $second = $this->booking();
        $this->assertSame('2', $this->badge($this->nav('/admin/bookings'), 'Bookings'));

        $this->post("/admin/bookings/{$first->id}/read")->assertRedirect('/admin/bookings');
        $this->assertSame('1', $this->badge($this->nav('/admin/bookings'), 'Bookings'));

        $this->get("/admin/bookings/{$second->id}")->assertOk();
        $this->assertNull($this->badge($this->nav('/admin/bookings'), 'Bookings'));
    }

    public function test_the_page_being_opened_already_shows_the_updated_number(): void
    {
        $this->admin();
        $message = $this->contact();
        $this->contact();

        // Opening message #1 marks it read BEFORE the page is drawn, so its own navbar already says 1.
        $this->assertSame('1', $this->badge($this->nav("/admin/messages/{$message->id}"), 'Messages'));
    }

    public function test_a_new_contact_form_message_and_a_new_booking_raise_the_badges(): void
    {
        Mail::fake();
        $package = Package::create(['name' => 'Angkor Wat Sunrise Tour', 'location' => 'Siem Reap', 'price' => 45, 'duration' => '1 Day', 'description' => 'Temples', 'status' => true]);
        $this->admin();
        $this->assertNull($this->badge($this->nav(), 'Messages'));
        $this->assertNull($this->badge($this->nav(), 'Bookings'));

        $this->post('/contact', ['name' => 'Mary', 'email' => 'mary@example.com', 'subject' => 'Question', 'message' => 'Do you run tours in December?'])->assertSessionHasNoErrors();
        $this->assertSame('1', $this->badge($this->nav(), 'Messages'));
        $this->assertNull($this->badge($this->nav(), 'Bookings'));

        $this->post('/booking', ['package_id' => $package->id, 'name' => 'Sok Dara', 'email' => 'dara@example.com', 'travel_date' => now()->addDays(9)->format('Y-m-d'), 'guests' => 2])->assertSessionHasNoErrors();
        $this->assertSame('1', $this->badge($this->nav(), 'Bookings'));

        $this->post('/contact', ['name' => 'Nita', 'email' => 'nita@example.com', 'subject' => 'Another', 'message' => 'Hello'])->assertSessionHasNoErrors();
        $this->assertSame('2', $this->badge($this->nav(), 'Messages'));
    }

    public function test_badges_appear_on_every_admin_page(): void
    {
        $this->admin();
        $package = Package::create(['name' => 'T', 'location' => 'L', 'price' => 1, 'duration' => '1 Day', 'description' => 'D', 'status' => true]);
        $message = $this->contact();
        $this->contact(read: true);
        $booking = $this->booking();
        $this->booking();

        // Two unread messages would be wrong: there is exactly 1 unread message and 2 unread bookings before we open anything.
        $pages = ['/admin/dashboard', '/admin/packages', '/admin/packages/create', "/admin/packages/{$package->id}/edit",
            '/admin/messages', '/admin/bookings', '/admin/banner'];

        foreach ($pages as $url) {
            $nav = $this->nav($url);
            $this->assertSame('1', $this->badge($nav, 'Messages'), "Messages badge on $url");
            $this->assertSame('2', $this->badge($nav, 'Bookings'), "Bookings badge on $url");
        }

        // The detail pages mark their own item read first, then draw the navbar.
        $this->assertNull($this->badge($this->nav("/admin/messages/{$message->id}"), 'Messages'));
        $this->assertSame('1', $this->badge($this->nav("/admin/bookings/{$booking->id}"), 'Bookings'));
    }

    public function test_links_still_work_and_badge_only_uses_red_for_itself(): void
    {
        $this->admin();
        $this->contact();
        $this->booking();

        $nav = $this->nav('/admin/messages');
        $this->assertStringContainsString('href="/admin/messages"', $nav);
        $this->assertStringContainsString('href="/admin/bookings"', $nav);
        $this->assertStringContainsString('aria-current="page"', $nav);   // active link still marked

        $html = $this->get('/admin/messages')->getContent();
        $this->assertStringContainsString('.site-nav .nav-badge { display: inline-block;', $html);
        $this->assertStringContainsString('background: #dc3545; color: #fff;', $html);
        $this->assertStringContainsString('border-radius: 50rem', $html);
        // The navbar colours themselves are unchanged (navy gradient + gold), no red anywhere else in the navbar CSS.
        $this->assertStringContainsString('background: linear-gradient(90deg, #00529B, #0A6CC0);', $html);
        $this->assertSame(1, substr_count(substr($html, strpos($html, '<style>'), strpos($html, '</style>') - strpos($html, '<style>')), '#dc3545'));
    }

    public function test_public_navbar_and_pages_never_show_badges_or_count_anything(): void
    {
        $this->admin();
        $this->contact();
        $this->booking();

        foreach (['/', '/packages', '/contact', '/booking'] as $url) {
            DB::enableQueryLog();
            DB::flushQueryLog();
            $html = $this->get($url)->assertOk()->getContent();
            $queries = collect(DB::getQueryLog())->pluck('query')->implode(' | ');

            $this->assertStringNotContainsString('<span class="nav-badge">', $html, "badge on public $url");
            $this->assertStringNotContainsString('from "contacts"', $queries, "contacts counted on public $url");
            $this->assertStringNotContainsString('from "bookings"', $queries, "bookings counted on public $url");
        }
    }

    public function test_guests_see_no_badges_on_the_login_page(): void
    {
        $this->contact();

        $html = $this->get('/admin/login')->assertOk()->getContent();

        $this->assertStringNotContainsString('<span class="nav-badge">', $html);
    }

    public function test_mobile_menu_markup_is_unchanged_and_contains_the_badges(): void
    {
        $this->admin();
        $this->contact();
        $this->booking();

        $nav = $this->nav();

        // Badges live inside the collapsible menu, which the hamburger button still controls.
        $this->assertStringContainsString('navbar-toggler', $nav);
        $this->assertStringContainsString('data-bs-target="#mainNav"', $nav);
        $menu = substr($nav, strpos($nav, 'id="mainNav"'));
        $this->assertStringContainsString('nav-badge', $menu);
        $this->assertStringContainsString('class="nav-logout"', $menu);
    }
}
