<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('bookings', function (Blueprint $table) {
            $table->id();

            // The package that was booked. If the admin deletes the package later,
            // package_id becomes NULL but package_name keeps the booking readable.
            $table->foreignId('package_id')->nullable()->constrained()->nullOnDelete();
            $table->string('package_name');

            $table->string('name');
            $table->string('email');
            $table->string('phone', 50)->nullable();
            $table->date('travel_date');
            $table->unsignedSmallInteger('guests');
            $table->text('message')->nullable();
            $table->boolean('is_read')->default(false);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('bookings');
    }
};
