<?php

namespace Tests\Feature;

use App\Models\Contact;
use App\Models\Package;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Testing\TestResponse;
use Tests\TestCase;

class AdminNavbarTest extends TestCase
{
    use RefreshDatabase;

    /** Only the <nav> element of a page, so we do not match links elsewhere on the page. */
    private function nav(TestResponse $response): string
    {
        $html = $response->getContent();
        $start = strpos($html, '<nav');

        return substr($html, $start, strpos($html, '</nav>', $start) - $start);
    }

    private function assertAdminNav(TestResponse $response): void
    {
        $response->assertOk();
        $nav = $this->nav($response);

        // Left side: the three admin links, in this order, before the Logout button.
        $positions = array_map(fn ($text) => strpos($nav, $text), [
            'href="/admin/packages"', 'href="/admin/messages"', 'href="/admin/banner"', 'class="nav-logout"',
        ]);
        $this->assertNotContains(false, $positions, 'An admin navbar item is missing.');
        $sorted = $positions;
        sort($sorted);
        $this->assertSame($sorted, $positions, 'Admin navbar items are in the wrong order.');
        $this->assertStringContainsString('>Packages</a>', $nav);
        $this->assertMatchesRegularExpression('#>Messages(<span class="nav-badge">[^<]*<span class="visually-hidden"> unread</span></span>)?</a>#', $nav);   // badge is optional
        $this->assertStringContainsString('>Banner Settings</a>', $nav);

        // Right side: Logout is a POST form with a CSRF token (not a GET link).
        $this->assertMatchesRegularExpression('#<form action="/admin/logout" method="POST"[^>]*>\s*<input type="hidden" name="_token"#', $nav);
        $this->assertStringContainsString('>Logout</button>', $nav);
        $this->assertStringContainsString('me-auto', $nav);   // links pushed to the left, Logout to the right

        // The public links are gone.
        $this->assertStringNotContainsString('>Home</a>', $nav);
        $this->assertStringNotContainsString('>Package</a>', $nav);
        $this->assertStringNotContainsString('>Contact</a>', $nav);

        // Mobile menu button is still there.
        $this->assertStringContainsString('navbar-toggler', $nav);
    }

    private function assertPublicNav(TestResponse $response): void
    {
        $response->assertOk();
        $nav = $this->nav($response);

        $this->assertMatchesRegularExpression('#href="/"[^>]*>Home</a>#', $nav);
        $this->assertMatchesRegularExpression('#href="/packages"[^>]*>Package</a>#', $nav);
        $this->assertMatchesRegularExpression('#href="/contact"[^>]*>Contact</a>#', $nav);
        $this->assertStringNotContainsString('Logout', $nav);
        $this->assertStringNotContainsString('/admin/', $nav);
    }

    public function test_admin_pages_show_the_admin_navbar(): void
    {
        $this->actingAs(User::factory()->create());
        $package = Package::create(['name' => 'T', 'location' => 'L', 'price' => 1, 'duration' => '1 Day', 'description' => 'D', 'status' => true]);
        $contact = Contact::create(['name' => 'N', 'email' => 'n@example.com', 'subject' => 'S', 'message' => 'M']);

        foreach (['/admin/dashboard', '/admin/packages', '/admin/packages/create', "/admin/packages/{$package->id}/edit",
            '/admin/messages', "/admin/messages/{$contact->id}", '/admin/banner'] as $url) {
            $this->assertAdminNav($this->get($url));
        }
    }

    public function test_active_admin_link_is_highlighted(): void
    {
        $this->actingAs(User::factory()->create());

        $this->assertMatchesRegularExpression('#nav-link active" href="/admin/packages"#', $this->nav($this->get('/admin/packages')));
        $this->assertMatchesRegularExpression('#nav-link active" href="/admin/messages"#', $this->nav($this->get('/admin/messages')));
        $this->assertMatchesRegularExpression('#nav-link active" href="/admin/banner"#', $this->nav($this->get('/admin/banner')));
        $this->assertStringNotContainsString('nav-link active', $this->nav($this->get('/admin/dashboard')));
    }

    public function test_admin_brand_goes_to_the_dashboard(): void
    {
        $this->actingAs(User::factory()->create());

        $this->assertStringContainsString('class="navbar-brand" href="/admin/dashboard"', $this->nav($this->get('/admin/packages')));
    }

    public function test_public_pages_keep_the_public_navbar_even_when_logged_in_as_admin(): void
    {
        $package = Package::create(['name' => 'T', 'location' => 'L', 'price' => 1, 'duration' => '1 Day', 'description' => 'D', 'status' => true]);

        foreach ([false, true] as $loggedIn) {
            if ($loggedIn) {
                $this->actingAs(User::factory()->create());
            }
            foreach (['/', '/packages', "/packages/{$package->id}", '/contact'] as $url) {
                $this->assertPublicNav($this->get($url));
            }
        }
    }

    public function test_public_navbar_brand_still_goes_home(): void
    {
        $this->assertStringContainsString('class="navbar-brand" href="/"', $this->nav($this->get('/')));
    }

    public function test_login_page_for_guests_uses_the_public_navbar(): void
    {
        $this->assertPublicNav($this->get('/admin/login'));
    }

    public function test_logout_only_works_as_post_and_ends_the_session(): void
    {
        $this->actingAs(User::factory()->create());

        $this->get('/admin/logout')->assertStatus(405);   // no GET logout link exists
        $this->assertAuthenticated();

        $this->post('/admin/logout')->assertRedirect('/admin/login');
        $this->assertGuest();

        $this->get('/admin/dashboard')->assertRedirect();  // logged out = sent to login
    }

    public function test_dashboard_has_no_duplicate_buttons_only_the_navbar_ones(): void
    {
        $this->actingAs(User::factory()->create());

        $response = $this->get('/admin/dashboard')->assertOk()
            ->assertSee('Admin Dashboard')->assertSee('Total Packages')->assertSee('Unread Messages');

        // Everything after the navbar (the page body): no menu buttons and no second logout form.
        $html = $response->getContent();
        $body = substr($html, strpos($html, '</nav>'));
        $this->assertStringNotContainsString('btn btn-outline-success', $body);
        $this->assertStringNotContainsString('/admin/logout', $body);
        $this->assertStringNotContainsString('Logout', $body);

        // The navbar still has all four.
        $this->assertAdminNav($response);
    }

    public function test_admin_pages_do_not_render_the_public_footer(): void
    {
        $this->actingAs(User::factory()->create());

        $response = $this->get('/admin/dashboard')->assertOk();
        $html = $response->getContent();

        $this->assertStringNotContainsString('site-footer', $html);
        $this->assertStringNotContainsString('Eocambo Tours. All rights reserved.', $html);
        $this->assertStringNotContainsString('<footer', $html);
    }

    public function test_every_admin_page_has_no_footer_and_every_public_page_keeps_it(): void
    {
        $package = Package::create(['name' => 'T', 'location' => 'L', 'price' => 1, 'duration' => '1 Day', 'description' => 'D', 'status' => true]);
        $contact = Contact::create(['name' => 'N', 'email' => 'n@example.com', 'subject' => 'S', 'message' => 'M']);

        // Public pages: footer is there.
        foreach (['/', '/packages', "/packages/{$package->id}", '/contact', '/booking'] as $url) {
            $this->get($url)->assertOk()->assertSee('<footer class="site-footer">', false)->assertSee('All rights reserved.');
        }

        // The admin login page (guest) has no footer either, but keeps the public navbar.
        $login = $this->get('/admin/login')->assertOk();
        $login->assertDontSee('<footer', false)->assertDontSee('site-footer', false)->assertDontSee('All rights reserved.');
        $this->assertPublicNav($login);
        $login->assertSee('Admin Login')->assertSee('action="/admin/login"', false);

        // Logged-in admin: public pages still have it, admin pages do not.
        $this->actingAs(User::factory()->create());
        foreach (['/', '/packages', '/contact'] as $url) {
            $this->get($url)->assertOk()->assertSee('<footer class="site-footer">', false);
        }
        foreach (['/admin/dashboard', '/admin/packages', '/admin/packages/create', "/admin/packages/{$package->id}/edit",
            '/admin/messages', "/admin/messages/{$contact->id}", '/admin/banner'] as $url) {
            $this->get($url)->assertOk()->assertDontSee('<footer', false)->assertDontSee('site-footer', false)->assertDontSee('All rights reserved.');
        }
    }
}
