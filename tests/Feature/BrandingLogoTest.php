<?php

namespace Tests\Feature;

use App\Models\BannerSetting;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

/**
 * The branding logo (Admin > Banner Settings > Branding) shown as a circle in the navbar, footer,
 * home banner and admin preview.
 */
class BrandingLogoTest extends TestCase
{
    use RefreshDatabase;

    private function formData(): array
    {
        return ['small_title' => "It's Time To", 'main_title' => 'TRAVEL', 'highlight_text' => 'EXPLORE', 'tagline' => 'Cambodia With Us!',
            'button_text' => 'BOOK NOW', 'button_url' => '/packages', 'include_1' => 'Local Tour Guide', 'discount' => '45',
            'offer_title' => 'SPECIAL TOUR OFFERS', 'offer_suffix' => 'OFF', 'show_offer' => '1', 'is_active' => '1'];
    }

    /** The <nav> and <footer> HTML of a page ('' if the page has no footer). */
    private function parts(string $html): array
    {
        $nav = substr($html, strpos($html, '<nav'), strpos($html, '</nav>') - strpos($html, '<nav'));
        $footer = str_contains($html, '<footer') ? substr($html, strpos($html, '<footer'), strpos($html, '</footer>') - strpos($html, '<footer')) : '';

        return [$nav, $footer];
    }

    private function uploadLogo(): string
    {
        Storage::fake('public');
        $this->actingAs(User::factory()->create())
            ->put('/admin/banner', $this->formData() + ['logo' => UploadedFile::fake()->image('logo.png', 300, 150)])
            ->assertSessionHasNoErrors();

        return BannerSetting::first()->logo;
    }

    public function test_without_a_logo_the_brand_name_is_shown_and_there_is_no_image(): void
    {
        foreach (['/', '/packages', '/contact', '/booking'] as $url) {
            [$nav, $footer] = $this->parts($this->get($url)->assertOk()->getContent());

            foreach ([$nav, $footer] as $part) {
                $this->assertStringContainsString('<span class="brand-name">Eocambo <span>Tours</span></span>', $part);
                $this->assertStringNotContainsString('<img', $part);
            }
        }
    }

    public function test_uploaded_logo_appears_in_the_navbar_and_footer_next_to_the_brand_name(): void
    {
        $logo = $this->uploadLogo();
        $this->assertStringStartsWith('banner/', $logo);
        Storage::disk('public')->assertExists($logo);

        foreach (['/', '/packages', '/contact', '/booking'] as $url) {
            [$nav, $footer] = $this->parts($this->get($url)->assertOk()->getContent());

            $this->assertStringContainsString('src="' . asset('storage/' . $logo) . '" alt="" class="brand-logo brand-logo-nav"', $nav, "navbar on $url");
            $this->assertStringContainsString('src="' . asset('storage/' . $logo) . '" alt="" class="brand-logo brand-logo-footer"', $footer, "footer on $url");
            // The brand name is still shown beside the logo, and the mobile menu button is untouched.
            $this->assertStringContainsString('<span class="brand-name">Eocambo <span>Tours</span></span>', $nav);
            $this->assertStringContainsString('<span class="brand-name">Eocambo <span>Tours</span></span>', $footer);
            $this->assertStringContainsString('navbar-toggler', $nav);
            $this->assertStringContainsString('id="mainNav"', $nav);
        }
    }

    public function test_logo_css_makes_it_a_circle_with_the_requested_sizes(): void
    {
        $html = $this->get('/')->getContent();

        $this->assertStringContainsString('.brand-logo { flex: none; border-radius: 50%; object-fit: cover;', $html);
        $this->assertStringContainsString('.brand-logo-nav { width: 45px; height: 45px;', $html);
        $this->assertStringContainsString('.brand-logo-footer { width: 56px; height: 56px;', $html);
        $this->assertStringContainsString('.eb-logo { width: 80px; height: 80px; border-radius: 50%; object-fit: cover;', $html);   // home banner logo
    }

    public function test_home_banner_logo_is_the_same_uploaded_logo(): void
    {
        $logo = $this->uploadLogo();

        $this->get('/')->assertSee('src="' . asset('storage/' . $logo) . '" alt="Eocambo Tours logo" class="eb-logo"', false);
    }

    public function test_admin_navbar_shows_the_logo_but_admin_pages_still_have_no_footer(): void
    {
        $logo = $this->uploadLogo();

        [$nav, $footer] = $this->parts($this->get('/admin/packages')->assertOk()->getContent());

        $this->assertStringContainsString('src="' . asset('storage/' . $logo) . '" alt="" class="brand-logo brand-logo-nav"', $nav);
        $this->assertStringContainsString('Logout', $nav);
        $this->assertSame('', $footer);
    }

    public function test_admin_branding_preview_is_a_90px_circle_and_other_previews_are_unchanged(): void
    {
        $this->assertStringNotContainsString('border-radius: 50%; object-fit: cover;" ', $this->actingAs(User::factory()->create())->get('/admin/banner')->getContent());  // nothing to preview yet

        Storage::fake('public');
        $this->put('/admin/banner', $this->formData() + [
            'logo' => UploadedFile::fake()->image('logo.png', 300, 150),
            'image_1' => UploadedFile::fake()->image('a.jpg'),
        ])->assertSessionHasNoErrors();
        $banner = BannerSetting::first();

        $html = $this->get('/admin/banner')->assertOk()->assertSee('1. Branding')->assertSee('Save Changes')->getContent();

        $this->assertStringContainsString('src="' . asset('storage/' . $banner->logo) . '" alt="Logo image" class="border" style="width: 90px; height: 90px; border-radius: 50%; object-fit: cover;"', $html);
        $this->assertStringContainsString('src="' . asset('storage/' . $banner->image_1) . '" alt="Image 1" class="img-thumbnail" style="max-width: 200px;"', $html);   // normal photos keep the old preview
        $this->assertStringContainsString('name="logo"', $html);   // the upload field is still there
    }

    public function test_a_logo_path_whose_file_is_missing_never_shows_a_broken_image(): void
    {
        Storage::fake('public');
        BannerSetting::first()->update(['logo' => 'banner/deleted-file.png']);   // path saved, but no file on disk

        foreach (['/', '/packages'] as $url) {
            [$nav, $footer] = $this->parts($this->get($url)->assertOk()->getContent());

            $this->assertStringNotContainsString('<img', $nav);
            $this->assertStringNotContainsString('<img', $footer);
            $this->assertStringContainsString('<span class="brand-name">Eocambo <span>Tours</span></span>', $nav);
        }
    }

    public function test_pages_still_work_when_there_is_no_banner_row_at_all(): void
    {
        BannerSetting::query()->delete();

        [$nav, $footer] = $this->parts($this->get('/')->assertOk()->getContent());

        $this->assertStringNotContainsString('<img', $nav);
        $this->assertStringContainsString('brand-name', $footer);
    }

    public function test_replacing_the_logo_updates_the_navbar_and_deletes_the_old_file(): void
    {
        $old = $this->uploadLogo();

        $this->put('/admin/banner', $this->formData() + ['logo' => UploadedFile::fake()->image('new.png')])->assertSessionHasNoErrors();
        $new = BannerSetting::first()->logo;

        $this->assertNotSame($old, $new);
        Storage::disk('public')->assertMissing($old);
        [$nav] = $this->parts($this->get('/')->getContent());
        $this->assertStringContainsString(asset('storage/' . $new), $nav);
        $this->assertStringNotContainsString($old, $nav);
    }
}
