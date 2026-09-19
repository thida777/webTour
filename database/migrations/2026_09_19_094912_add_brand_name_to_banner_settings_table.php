<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * The brand name shown next to the logo in the navbar and footer. The default means the existing
     * settings row automatically gets "Eocambo Tours", so nothing changes until the admin edits it.
     */
    public function up(): void
    {
        Schema::table('banner_settings', function (Blueprint $table) {
            $table->string('brand_name')->nullable()->default('Eocambo Tours')->after('logo');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('banner_settings', function (Blueprint $table) {
            $table->dropColumn('brand_name');
        });
    }
};
