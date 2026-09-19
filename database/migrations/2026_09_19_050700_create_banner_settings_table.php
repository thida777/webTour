<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * The website only ever has ONE banner settings row. The column defaults below
     * are the starting content, and one default row is inserted at the end.
     */
    public function up(): void
    {
        Schema::create('banner_settings', function (Blueprint $table) {
            $table->id();

            // Branding (file paths inside storage/app/public/banner)
            $table->string('logo')->nullable();

            // Headline
            $table->string('small_title')->nullable()->default("It's Time To");
            $table->string('main_title')->nullable()->default('TRAVEL');
            $table->string('highlight_text')->nullable()->default('EXPLORE');
            $table->string('tagline')->nullable()->default('Cambodia With Us!');

            // Book Now button
            $table->string('button_text')->nullable()->default('BOOK NOW');
            $table->string('button_url')->nullable()->default('/packages');

            // Contact
            $table->string('phone', 50)->nullable();
            $table->string('website')->nullable();

            // Three collage images (file paths)
            $table->string('image_1')->nullable();
            $table->string('image_2')->nullable();
            $table->string('image_3')->nullable();

            // "Our tours include" list
            $table->string('include_1')->nullable()->default('Local Tour Guide');
            $table->string('include_2')->nullable()->default('Transportation');
            $table->string('include_3')->nullable()->default('Amazing Destinations');
            $table->string('include_4')->nullable()->default('Flexible Tour Packages');

            // Special offer
            $table->string('offer_title')->nullable()->default('SPECIAL TOUR OFFERS');
            $table->decimal('discount', 5, 2)->nullable()->default(45);
            $table->string('offer_suffix')->nullable()->default('OFF');
            $table->boolean('show_offer')->default(true);

            // Social links (left empty until the admin adds real ones)
            $table->string('facebook_url')->nullable();
            $table->string('instagram_url')->nullable();
            $table->string('youtube_url')->nullable();
            $table->string('x_url')->nullable();

            // Show / hide the whole banner
            $table->boolean('is_active')->default(true);

            $table->timestamps();
        });

        // The single default row (all other columns use the defaults above).
        DB::table('banner_settings')->insert([
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('banner_settings');
    }
};
