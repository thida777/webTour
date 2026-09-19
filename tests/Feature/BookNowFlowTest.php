<?php

namespace Tests\Feature;

use App\Mail\BookingRequestMail;
use App\Models\Booking;
use App\Models\Package;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Mail;
use Tests\TestCase;

/**
 * Package -> BOOK NOW -> /booking (right package selected, right image) -> submit.
 * (The image swap on dropdown change is JavaScript; it is checked in a real browser, and here we check
 *  that every option carries the data the script needs.)
 */
class BookNowFlowTest extends TestCase
{
    use RefreshDatabase;

    private function package(string $name, ?string $image, bool $active = true): Package
    {
        return Package::create(['name' => $name, 'location' => 'Cambodia', 'price' => 40, 'duration' => '1 Day',
            'description' => 'A lovely tour.', 'status' => $active, 'image' => $image]);
    }

    /** All <a> tags inside one package card: [href => text]. */
    private function cardLinks(string $html, string $packageName): array
    {
        preg_match_all('#<div class="card h-100 shadow-sm package-card">(.*?)</div>\s*</div>\s*</div>#s', $html, $cards);
        foreach ($cards[1] as $card) {
            if (str_contains($card, '>' . $packageName . '</a>')) {
                preg_match_all('#<a href="([^"]+)"[^>]*>([^<]*)</a>#', $card, $m, PREG_SET_ORDER);

                return array_column($m, 2, 1);
            }
        }

        return [];
    }

    public function test_every_package_card_has_a_book_now_button_for_its_own_package(): void
    {
        $koh = $this->package('Koh Rong Island Escape', 'packages/koh.jpg');
        $angkor = $this->package('Angkor Wat Sunrise Tour', 'packages/angkor.jpg');
        $noImage = $this->package('No Photo Tour', null);
        $this->package('Hidden Tour', 'packages/hidden.jpg', false);

        $html = $this->get('/packages')->assertOk()->getContent();

        $this->assertSame(3, substr_count($html, '>BOOK NOW</a>'));       // one per visible package, none for the hidden one
        foreach ([$koh, $angkor, $noImage] as $package) {
            $links = $this->cardLinks($html, $package->name);
            $this->assertSame(
                ["/packages/{$package->id}" => $package->name, "/booking?package={$package->id}" => 'BOOK NOW'],
                $links,
                "Card for {$package->name} must have exactly a details link and its own BOOK NOW link."
            );
        }
    }

    public function test_package_cards_keep_the_whole_card_link_and_have_no_nested_links(): void
    {
        $this->package('Koh Rong Island Escape', 'packages/koh.jpg');

        $html = $this->get('/packages')->getContent();

        // The title link is stretched over the whole card, so image / text / empty space all open the details page.
        $this->assertStringContainsString('class="stretched-link text-decoration-none text-reset"', $html);
        $this->assertSame(0, preg_match('/<a\b[^>]*>(?:(?!<\/a>).)*<a\b/s', $html), 'Links must not be nested.');
        $this->assertStringContainsString('position-relative z-2', $html);   // BOOK NOW sits above the stretched link
    }

    public function test_package_pages_have_no_contact_us_button(): void
    {
        $package = $this->package('Koh Rong Island Escape', 'packages/koh.jpg');

        // Nothing inside the page content links to /contact (the navbar and footer are outside <main>).
        foreach (['/packages', "/packages/{$package->id}"] as $url) {
            $html = $this->get($url)->getContent();
            $main = substr($html, strpos($html, '<main'), strpos($html, '</main>') - strpos($html, '<main'));
            $this->assertStringNotContainsString('href="/contact"', $main);
            $this->assertStringNotContainsString('Contact Us', $main);
        }
    }

    public function test_package_detail_page_has_book_now_for_that_package(): void
    {
        $koh = $this->package('Koh Rong Island Escape', 'packages/koh.jpg');
        $other = $this->package('Other Tour', null);

        $this->get("/packages/{$koh->id}")->assertOk()
            ->assertSee("/booking?package={$koh->id}\" class=\"btn btn-success btn-lg mt-2\">BOOK NOW", false)
            ->assertDontSee("/booking?package={$other->id}", false)
            ->assertDontSee('class="btn btn-outline-success btn-lg mt-2">Contact Us', false);   // the package Contact Us button is gone

        // Inactive packages are still hidden (existing behaviour).
        $hidden = $this->package('Hidden Tour', null, false);
        $this->get("/packages/{$hidden->id}")->assertNotFound();
    }

    public function test_booking_page_preselects_the_clicked_package_and_shows_its_image(): void
    {
        $angkor = $this->package('Angkor Wat Sunrise Tour', 'packages/angkor.jpg');
        $koh = $this->package('Koh Rong Island Escape', 'packages/koh.jpg');

        $html = $this->get("/booking?package={$koh->id}")->assertOk()->getContent();

        // Dropdown: Koh Rong selected, Angkor not.
        $this->assertStringContainsString("value=\"{$koh->id}\" selected", $html);
        $this->assertStringNotContainsString("value=\"{$angkor->id}\" selected", $html);

        // Image above the dropdown is Koh Rong's stored image, visible (not hidden).
        $this->assertMatchesRegularExpression('#<div id="package-preview" class="mb-3 ">\s*<img id="package-preview-img" src="[^"]*/storage/packages/koh\.jpg" alt="Koh Rong Island Escape"#', $html);
        $this->assertStringNotContainsString('angkor.jpg" alt=', $html);
    }

    public function test_every_option_carries_its_own_image_url_for_the_dropdown_script(): void
    {
        $angkor = $this->package('Angkor Wat Sunrise Tour', 'packages/angkor.jpg');
        $koh = $this->package('Koh Rong Island Escape', 'packages/koh.jpg');
        $noImage = $this->package('No Photo Tour', null);

        $html = $this->get('/booking')->assertOk()->getContent();

        $this->assertMatchesRegularExpression("#value=\"{$angkor->id}\"[^>]*data-name=\"Angkor Wat Sunrise Tour\" data-image=\"[^\"]*/storage/packages/angkor\.jpg\"#", $html);
        $this->assertMatchesRegularExpression("#value=\"{$koh->id}\"[^>]*data-name=\"Koh Rong Island Escape\" data-image=\"[^\"]*/storage/packages/koh\.jpg\"#", $html);
        $this->assertMatchesRegularExpression("#value=\"{$noImage->id}\"[^>]*data-image=\"\"#", $html);   // no image -> script hides the preview

        // Nothing selected yet: preview is hidden, and the change script is on the page.
        $this->assertStringContainsString('id="package-preview" class="mb-3 d-none"', $html);
        $this->assertStringContainsString("addEventListener('change'", $html);
    }

    public function test_preview_is_hidden_for_a_package_without_an_image_or_an_unknown_package(): void
    {
        $noImage = $this->package('No Photo Tour', null);

        $this->get("/booking?package={$noImage->id}")->assertSee('id="package-preview" class="mb-3 d-none"', false)
            ->assertSee("value=\"{$noImage->id}\" selected", false);

        $this->get('/booking?package=9999')->assertOk()->assertSee('id="package-preview" class="mb-3 d-none"', false);
        $this->get('/booking?package=abc')->assertOk();
    }

    public function test_inactive_package_is_not_preselected_or_shown(): void
    {
        $hidden = $this->package('Hidden Tour', 'packages/hidden.jpg', false);
        $this->package('Visible Tour', null);

        $html = $this->get("/booking?package={$hidden->id}")->assertOk()->getContent();

        $this->assertStringNotContainsString('Hidden Tour', $html);
        $this->assertStringNotContainsString('hidden.jpg', $html);
    }

    public function test_image_and_selection_are_kept_after_a_validation_error(): void
    {
        $koh = $this->package('Koh Rong Island Escape', 'packages/koh.jpg');

        $this->from('/booking')->post('/booking', ['package_id' => $koh->id, 'name' => '', 'email' => 'bad', 'travel_date' => '', 'guests' => 2])
            ->assertRedirect('/booking')->assertSessionHasErrors(['name', 'email', 'travel_date']);

        $this->get('/booking')->assertSee("value=\"{$koh->id}\" selected", false)->assertSee('/storage/packages/koh.jpg" alt="Koh Rong Island Escape"', false);
    }

    public function test_full_flow_book_now_then_submit_saves_the_right_package_and_emails_the_admin(): void
    {
        Mail::fake();
        $this->package('Angkor Wat Sunrise Tour', 'packages/angkor.jpg');
        $koh = $this->package('Koh Rong Island Escape', 'packages/koh.jpg');

        // 1. Package list -> follow the BOOK NOW link of the Koh Rong card.
        $html = $this->get('/packages')->getContent();
        $bookNow = array_search('BOOK NOW', $this->cardLinks($html, 'Koh Rong Island Escape'), true);
        $this->assertSame("/booking?package={$koh->id}", $bookNow);

        // 2. Booking page shows Koh Rong selected.
        $this->get($bookNow)->assertOk()->assertSee("value=\"{$koh->id}\" selected", false);

        // 3. Submit exactly what the form would send (package_id comes from the selected dropdown option).
        $this->post('/booking', [
            'package_id' => $koh->id, 'name' => 'Sok Dara', 'email' => 'dara@example.com', 'phone' => '012 345 678',
            'travel_date' => now()->addDays(14)->format('Y-m-d'), 'guests' => 2, 'message' => 'Beach please',
        ])->assertRedirect('/booking')->assertSessionHas('success');

        $booking = Booking::first();
        $this->assertSame($koh->id, $booking->package_id);
        $this->assertSame('Koh Rong Island Escape', $booking->package_name);
        Mail::assertSent(BookingRequestMail::class, fn ($mail) => $mail->hasTo('touradmin@eocambo.dev')
            && $mail->hasSubject('New Booking Request: Koh Rong Island Escape - Sok Dara'));
    }
}
