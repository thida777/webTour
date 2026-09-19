<?php

namespace Tests\Feature;

use App\Models\BannerSetting;
use App\Models\Package;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class BannerSettingsTest extends TestCase
{
    use RefreshDatabase;

    /** Every field the form sends (text only). */
    private function formData(array $override = []): array
    {
        return array_merge([
            'small_title' => "It's Time To",
            'main_title' => 'TRAVEL',
            'highlight_text' => 'EXPLORE',
            'tagline' => 'Cambodia With Us!',
            'button_text' => 'BOOK NOW',
            'button_url' => '/packages',
            'include_1' => 'Local Tour Guide',
            'include_2' => 'Transportation',
            'include_3' => 'Amazing Destinations',
            'include_4' => 'Flexible Tour Packages',
            'offer_title' => 'SPECIAL TOUR OFFERS',
            'discount' => '45',
            'offer_suffix' => 'OFF',
            'show_offer' => '1',
            'is_active' => '1',
        ], $override);
    }

    private function admin(): User
    {
        return User::factory()->create();
    }

    public function test_default_banner_row_exists_after_migration(): void
    {
        $this->assertSame(1, BannerSetting::count());
        $banner = BannerSetting::first();
        $this->assertSame('TRAVEL', $banner->main_title);
        $this->assertSame(45.0, $banner->discount);
        $this->assertTrue($banner->is_active);
        $this->assertNull($banner->facebook_url);
    }

    public function test_home_page_shows_default_banner_and_book_now_goes_to_packages(): void
    {
        $this->get('/')
            ->assertOk()
            ->assertSee('TRAVEL')
            ->assertSee('EXPLORE')
            ->assertSee('Cambodia With Us!')
            ->assertSee('OUR TOURS INCLUDE')
            ->assertSee('Local Tour Guide')
            ->assertSee('SPECIAL TOUR OFFERS')
            ->assertSee('45%')
            ->assertSee('<a href="/packages" class="eb-btn">BOOK NOW</a>', false)
            ->assertDontSee('FOLLOW US'); // no social links saved yet
    }

    public function test_home_page_still_works_when_no_banner_row_exists(): void
    {
        BannerSetting::query()->delete();

        $this->get('/')->assertOk()->assertSee('Featured Tours')->assertDontSee('eb-banner', false);
    }

    public function test_home_page_keeps_featured_tours_and_why_choose_us(): void
    {
        foreach (['A', 'B', 'C', 'D'] as $i => $name) {
            Package::create(['name' => "Tour $name", 'location' => 'Loc', 'price' => 10, 'duration' => '1 Day',
                'description' => 'Desc', 'status' => true, 'image' => null])
                ->forceFill(['created_at' => now()->subDays(10 - $i)])->save();
        }
        Package::create(['name' => 'Hidden Tour', 'location' => 'Loc', 'price' => 10, 'duration' => '1 Day',
            'description' => 'Desc', 'status' => false]);

        $this->get('/')
            ->assertOk()
            ->assertSee('Featured Tours')
            ->assertSee('Why Choose Us')
            ->assertSee('Tour D')->assertSee('Tour C')->assertSee('Tour B')
            ->assertDontSee('Tour A')
            ->assertDontSee('Hidden Tour');
    }

    public function test_guest_cannot_open_or_save_banner_settings(): void
    {
        $this->get('/admin/banner')->assertRedirect();
        $this->put('/admin/banner', $this->formData(['main_title' => 'HACKED']))->assertRedirect();
        $this->assertSame('TRAVEL', BannerSetting::first()->main_title);
    }

    public function test_admin_can_login_and_open_dashboard_and_banner_page(): void
    {
        $user = User::factory()->create(['password' => bcrypt('secret-pass-1')]);

        $this->post('/admin/login', ['email' => $user->email, 'password' => 'secret-pass-1'])
            ->assertRedirect('/admin/dashboard');

        $this->get('/admin/dashboard')->assertOk()->assertSee('href="/admin/banner"', false)->assertSee('Banner Settings');
        $this->get('/admin/banner')->assertOk()->assertSee('Save Changes')->assertSee('Banner Visibility');
    }

    public function test_admin_can_save_text_offer_includes_and_social_links(): void
    {
        $this->actingAs($this->admin())
            ->put('/admin/banner', $this->formData([
                'main_title' => 'DISCOVER',
                'highlight_text' => 'ANGKOR',
                'phone' => '+855 12 345 678',
                'website' => 'https://www.example.com',
                'discount' => '30',
                'include_1' => 'Free Breakfast',
                'facebook_url' => 'https://www.facebook.com/example',
                'youtube_url' => 'https://www.youtube.com/@example',
            ]))
            ->assertRedirect('/admin/banner')
            ->assertSessionHas('success');

        $this->assertSame(1, BannerSetting::count()); // still only one row

        $this->get('/')
            ->assertSee('DISCOVER')->assertSee('ANGKOR')
            ->assertSee('+855 12 345 678')->assertSee('href="tel:+85512345678"', false)
            ->assertSee('30%')->assertSee('Free Breakfast')
            ->assertSee('https://www.facebook.com/example', false)
            ->assertSee('https://www.youtube.com/@example', false)
            ->assertSee('FOLLOW US')
            ->assertDontSee('instagram.com');
    }

    public function test_admin_can_upload_and_replace_logo_and_three_images(): void
    {
        Storage::fake('public');
        $admin = $this->admin();

        $this->actingAs($admin)->put('/admin/banner', $this->formData() + [
            'logo' => UploadedFile::fake()->image('logo.png'),
            'image_1' => UploadedFile::fake()->image('a.jpg'),
            'image_2' => UploadedFile::fake()->image('b.jpg'),
            'image_3' => UploadedFile::fake()->image('c.jpg'),
        ])->assertRedirect('/admin/banner')->assertSessionHasNoErrors();

        $banner = BannerSetting::first();
        foreach (['logo', 'image_1', 'image_2', 'image_3'] as $field) {
            $this->assertStringStartsWith('banner/', $banner->$field);
            Storage::disk('public')->assertExists($banner->$field);
        }

        $this->get('/')
            ->assertSee('storage/' . $banner->logo, false)
            ->assertSee('storage/' . $banner->image_1, false)
            ->assertSee('storage/' . $banner->image_2, false)
            ->assertSee('storage/' . $banner->image_3, false);
        $this->get('/admin/banner')->assertSee('storage/' . $banner->image_1, false); // preview

        // Replace image 1: old file deleted, others kept.
        $oldImage1 = $banner->image_1;
        $oldImage2 = $banner->image_2;
        $this->actingAs($admin)->put('/admin/banner', $this->formData() + [
            'image_1' => UploadedFile::fake()->image('new.jpg'),
        ])->assertSessionHasNoErrors();

        $banner->refresh();
        $this->assertNotSame($oldImage1, $banner->image_1);
        Storage::disk('public')->assertMissing($oldImage1);
        Storage::disk('public')->assertExists($banner->image_1);
        $this->assertSame($oldImage2, $banner->image_2); // untouched when no new file
        Storage::disk('public')->assertExists($oldImage2);
    }

    public function test_empty_image_slots_show_no_broken_img_tag(): void
    {
        $html = $this->get('/')->getContent();
        $this->assertStringNotContainsString('storage/', $html);
        $this->assertStringContainsString('eb-photo-empty', $html);
        $this->assertStringNotContainsString('eb-brand', $html); // the banner has no logo or brand block (the navbar shows the brand)
    }

    public function test_admin_can_hide_offer_then_hide_banner_then_show_again(): void
    {
        $admin = $this->admin();

        $this->actingAs($admin)->put('/admin/banner', $this->formData(['show_offer' => '0']))->assertSessionHasNoErrors();
        $this->get('/')->assertSee('TRAVEL')->assertDontSee('SPECIAL TOUR OFFERS');

        $this->actingAs($admin)->put('/admin/banner', $this->formData(['is_active' => '0']))->assertSessionHasNoErrors();
        $this->get('/')->assertOk()->assertDontSee('eb-banner', false)->assertSee('Featured Tours');

        $this->actingAs($admin)->put('/admin/banner', $this->formData(['is_active' => '1', 'show_offer' => '1']));
        $this->get('/')->assertSee('eb-banner', false)->assertSee('SPECIAL TOUR OFFERS');
    }

    public function test_validation_rejects_bad_input(): void
    {
        Storage::fake('public');
        $admin = $this->admin();

        $this->actingAs($admin)->put('/admin/banner', $this->formData([
            'button_url' => 'javascript:alert(1)',
            'website' => 'not a url',
            'facebook_url' => 'javascript:alert(1)',
            'discount' => '150',
            'phone' => str_repeat('1', 51),
            'main_title' => str_repeat('x', 256),
        ]) + [
            'image_1' => UploadedFile::fake()->create('evil.php', 10, 'application/x-php'),
            'image_2' => UploadedFile::fake()->image('big.jpg')->size(3000),
            'image_3' => UploadedFile::fake()->create('logo.svg', 10, 'image/svg+xml'),
        ])->assertSessionHasErrors(['button_url', 'website', 'facebook_url', 'discount', 'phone', 'main_title', 'image_1', 'image_2', 'image_3']);

        $this->assertSame('TRAVEL', BannerSetting::first()->main_title); // nothing was saved
    }

    public function test_button_url_can_be_full_https_address(): void
    {
        $this->actingAs($this->admin())
            ->put('/admin/banner', $this->formData(['button_url' => 'https://example.com/deals']))
            ->assertSessionHasNoErrors();

        $this->get('/')->assertSee('<a href="https://example.com/deals" class="eb-btn">', false);
    }

    public function test_banner_html_has_no_nested_links(): void
    {
        $this->actingAs($this->admin())->put('/admin/banner', $this->formData([
            'phone' => '012 345 678', 'website' => 'https://example.com',
            'facebook_url' => 'https://facebook.com/x', 'instagram_url' => 'https://instagram.com/x',
            'youtube_url' => 'https://youtube.com/x', 'x_url' => 'https://x.com/x',
        ]));

        $html = $this->get('/')->getContent();
        $banner = substr($html, strpos($html, '<section class="eb-banner"'), strpos($html, '</section>', strpos($html, '<section class="eb-banner"')) - strpos($html, '<section class="eb-banner"'));
        $this->assertSame(0, preg_match('/<a\b[^>]*>(?:(?!<\/a>).)*<a\b/s', $banner));
        $this->assertSame(4, substr_count($banner, 'aria-label="Eocambo Tours on '));
    }

    public function test_existing_public_pages_still_work(): void
    {
        $package = Package::create(['name' => 'Angkor Tour', 'location' => 'Siem Reap', 'price' => 45, 'duration' => '1 Day',
            'description' => 'Temples', 'status' => true]);

        $this->get('/packages')->assertOk()->assertSee('Angkor Tour');
        $this->get('/packages/' . $package->id)->assertOk()->assertSee('Angkor Tour');
        $this->get('/contact')->assertOk();
        $this->get('/admin/login')->assertOk();
    }

    public function test_existing_admin_pages_still_work(): void
    {
        $this->actingAs($this->admin());

        $this->get('/admin/packages')->assertOk();
        $this->get('/admin/messages')->assertOk();
    }
}
