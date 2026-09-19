<?php

namespace Tests\Feature;

use App\Models\BannerSetting;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

/**
 * The brand name shown next to the logo (navbar + footer), edited in Admin > Banner Settings > Branding.
 */
class BrandNameTest extends TestCase
{
    use RefreshDatabase;

    private function formData(array $override = []): array
    {
        return $override + ['small_title' => "It's Time To", 'main_title' => 'TRAVEL', 'highlight_text' => 'EXPLORE', 'tagline' => 'Cambodia With Us!',
            'button_text' => 'BOOK NOW', 'button_url' => '/packages', 'include_1' => 'Local Tour Guide', 'discount' => '45',
            'offer_title' => 'SPECIAL TOUR OFFERS', 'offer_suffix' => 'OFF', 'show_offer' => '1', 'is_active' => '1'];
    }

    private function save(array $override = [], array $files = [])
    {
        return $this->actingAs(User::factory()->create())->put('/admin/banner', $this->formData($override) + $files);
    }

    /** [navbar html, footer html] of a page. */
    private function parts(string $url): array
    {
        $html = $this->get($url)->assertOk()->getContent();
        $nav = substr($html, strpos($html, '<nav'), strpos($html, '</nav>') - strpos($html, '<nav'));
        $footer = str_contains($html, '<footer') ? substr($html, strpos($html, '<footer'), strpos($html, '</footer>') - strpos($html, '<footer')) : '';

        return [$nav, $footer, $html];
    }

    public function test_the_default_name_is_eocambo_tours_and_looks_exactly_as_before(): void
    {
        $this->assertSame('Eocambo Tours', BannerSetting::first()->brand_name);   // the migration default

        [$nav, $footer, $html] = $this->parts('/');

        $this->assertStringContainsString('<span class="brand-name">Eocambo <span>Tours</span></span>', $nav);
        $this->assertStringContainsString('<span class="brand-name">Eocambo <span>Tours</span></span>', $footer);
        $this->assertStringContainsString('&copy; ' . date('Y') . ' Eocambo Tours. All rights reserved.', str_replace('©', '&copy;', $html));
    }

    public function test_the_admin_form_has_a_brand_name_field_in_the_branding_section_with_the_current_name(): void
    {
        $this->actingAs(User::factory()->create());

        $html = $this->get('/admin/banner')->assertOk()->assertSee('1. Branding')->getContent();

        $this->assertMatchesRegularExpression('#<input type="text" id="brand_name" name="brand_name" value="Eocambo Tours"#', $html);
        // It sits inside the Branding card, before the logo upload.
        $this->assertLessThan(strpos($html, 'name="logo"'), strpos($html, 'name="brand_name"'));
        $this->assertLessThan(strpos($html, '2. Headline'), strpos($html, 'name="brand_name"'));
    }

    public function test_admin_can_change_the_name_and_it_shows_in_navbar_footer_copyright_and_admin_navbar(): void
    {
        $this->save(['brand_name' => 'Kampot Travel Co'])->assertRedirect('/admin/banner')->assertSessionHas('success');
        $this->assertSame('Kampot Travel Co', BannerSetting::first()->brand_name);

        foreach (['/', '/packages', '/contact', '/booking'] as $url) {
            [$nav, $footer, $html] = $this->parts($url);

            // Everything before the last word is white, the last word is gold (inside the span).
            $this->assertStringContainsString('<span class="brand-name">Kampot Travel <span>Co</span></span>', $nav, "navbar on $url");
            $this->assertStringContainsString('<span class="brand-name">Kampot Travel <span>Co</span></span>', $footer, "footer on $url");
            $this->assertStringContainsString('Kampot Travel Co. All rights reserved.', $html);
            $this->assertStringNotContainsString('<span>Tours</span>', $nav);
        }

        // The admin navbar (same layout) shows it too, and the form field shows the saved value.
        [$adminNav] = $this->parts('/admin/packages');
        $this->assertStringContainsString('<span class="brand-name">Kampot Travel <span>Co</span></span>', $adminNav);
        $this->get('/admin/banner')->assertSee('name="brand_name" value="Kampot Travel Co"', false);
    }

    public function test_a_one_word_name_is_shown_in_white_without_a_gold_span(): void
    {
        $this->save(['brand_name' => 'Angkor']);

        [$nav, $footer] = $this->parts('/');

        $this->assertStringContainsString('<span class="brand-name">Angkor</span>', $nav);
        $this->assertStringContainsString('<span class="brand-name">Angkor</span>', $footer);
    }

    public function test_extra_spaces_are_ignored(): void
    {
        $this->save(['brand_name' => '   Khmer    Wonder   Tours  ']);

        [$nav] = $this->parts('/');

        $this->assertStringContainsString('<span class="brand-name">Khmer Wonder <span>Tours</span></span>', $nav);
    }

    public function test_emptying_the_name_goes_back_to_eocambo_tours(): void
    {
        $this->save(['brand_name' => 'Something Else']);
        $this->save(['brand_name' => ''])->assertSessionHasNoErrors();

        [$nav, $footer, $html] = $this->parts('/');

        $this->assertStringContainsString('<span class="brand-name">Eocambo <span>Tours</span></span>', $nav);
        $this->assertStringContainsString('<span class="brand-name">Eocambo <span>Tours</span></span>', $footer);
        $this->assertStringContainsString('Eocambo Tours. All rights reserved.', $html);
    }

    public function test_a_null_name_row_and_a_missing_row_both_fall_back_safely(): void
    {
        BannerSetting::first()->update(['brand_name' => null]);
        $this->assertStringContainsString('<span class="brand-name">Eocambo <span>Tours</span></span>', $this->parts('/')[0]);

        BannerSetting::query()->delete();
        $this->assertStringContainsString('<span class="brand-name">Eocambo <span>Tours</span></span>', $this->parts('/')[0]);
    }

    public function test_the_name_is_escaped_so_it_cannot_inject_html(): void
    {
        $this->save(['brand_name' => '<script>alert(1)</script> Tours & "Co"']);

        [$nav, $footer, $html] = $this->parts('/');

        $this->assertStringNotContainsString('<script>alert(1)', $nav . $footer);
        $this->assertStringContainsString('&lt;script&gt;alert(1)&lt;/script&gt;', $nav);
        $this->assertStringContainsString('&amp;', $nav);
        $this->assertStringContainsString('<span>&quot;Co&quot;</span>', $nav);
    }

    public function test_the_name_is_limited_to_60_characters(): void
    {
        $this->save(['brand_name' => str_repeat('a', 61)])->assertSessionHasErrors('brand_name');
        $this->assertSame('Eocambo Tours', BannerSetting::first()->brand_name);   // nothing was saved

        $this->save(['brand_name' => str_repeat('a', 60)])->assertSessionHasNoErrors();
    }

    public function test_the_layout_lets_a_long_name_wrap_instead_of_overflowing_the_screen(): void
    {
        $this->save(['brand_name' => 'Angkor Heritage Discovery Tours Travel Cambodia Company Ltd']);

        $html = $this->get('/')->assertOk()->getContent();

        // Bootstrap forces white-space: nowrap on .navbar-brand; without these rules a long name pushes the page wider than a phone.
        $this->assertStringContainsString('.site-nav .navbar-brand { min-width: 0; white-space: normal; }', $html);
        $this->assertStringContainsString('.brand-name { min-width: 0; overflow-wrap: anywhere; }', $html);
        $this->assertStringContainsString('.site-nav .navbar-brand { flex: 1 1 0;', $html);   // on phones the name shares the row with the menu button
    }

    public function test_guests_cannot_change_the_name(): void
    {
        $this->put('/admin/banner', $this->formData(['brand_name' => 'Hacked']))->assertRedirect('/admin/login');

        $this->assertSame('Eocambo Tours', BannerSetting::first()->brand_name);
    }

    public function test_the_name_and_the_logo_work_together_and_saving_a_name_keeps_the_logo(): void
    {
        Storage::fake('public');
        $this->save(['brand_name' => 'Kampot Travel'], ['logo' => UploadedFile::fake()->image('logo.png')]);
        $logo = BannerSetting::first()->logo;

        [$nav, $footer] = $this->parts('/');
        $this->assertStringContainsString('class="brand-logo brand-logo-nav"', $nav);
        $this->assertStringContainsString('<span class="brand-name">Kampot <span>Travel</span></span>', $nav);
        $this->assertStringContainsString('class="brand-logo brand-logo-footer"', $footer);

        // Changing only the name later (no new file) keeps the logo.
        $this->save(['brand_name' => 'Kampot Tours']);
        $this->assertSame($logo, BannerSetting::first()->logo);
        $this->assertStringContainsString('class="brand-logo brand-logo-nav"', $this->parts('/')[0]);
    }

    public function test_saving_the_other_banner_settings_does_not_disturb_the_name(): void
    {
        $this->save(['brand_name' => 'Kampot Travel', 'main_title' => 'DISCOVER']);

        $this->get('/')->assertSee('DISCOVER');
        $this->assertSame('Kampot Travel', BannerSetting::first()->brand_name);
        $this->assertStringContainsString('<span class="brand-name">Kampot <span>Travel</span></span>', $this->parts('/')[0]);
    }

    public function test_admin_pages_still_have_no_footer_and_the_public_badge_free_navbar_is_intact(): void
    {
        $this->save(['brand_name' => 'Kampot Travel']);

        $this->get('/admin/packages')->assertOk()->assertDontSee('<footer', false);
        [$nav] = $this->parts('/');
        $this->assertMatchesRegularExpression('#href="/"[^>]*>Home</a>#', $nav);
        $this->assertMatchesRegularExpression('#href="/packages"[^>]*>Package</a>#', $nav);
        $this->assertMatchesRegularExpression('#href="/contact"[^>]*>Contact</a>#', $nav);
    }
}
