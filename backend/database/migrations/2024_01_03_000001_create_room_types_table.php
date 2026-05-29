<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * room_types is the foundation for pricing, reservations, inventory,
     * and channel manager integrations (SiteMinder, Booking.com, Expedia, Airbnb).
     */
    public function up(): void
    {
        Schema::create('room_types', function (Blueprint $table) {
            $table->id();

            // ── Basic Information ───────────────────────────────────────────────
            $table->string('name');
            $table->text('description')->nullable();

            // ── Pricing ─────────────────────────────────────────────────────────
            $table->decimal('base_price', 10, 2)->default(0.00);

            // ── Occupancy ───────────────────────────────────────────────────────
            $table->unsignedInteger('max_adults')->default(2);
            $table->unsignedInteger('max_children')->default(0);
            $table->unsignedInteger('max_occupancy')->default(2);

            // ── Room Details ─────────────────────────────────────────────────────
            $table->string('bed_type')->nullable();
            $table->json('amenities')->nullable();

            // ── Images ──────────────────────────────────────────────────────────
            $table->json('images')->nullable();

            // ── Status ─────────────────────────────────────────────────────────
            $table->boolean('is_active')->default(true);

            // ── Channel Manager Integration (Future) ───────────────────────────
            // These fields prepare the room type for synchronization with
            // SiteMinder, Booking.com, Expedia, Airbnb, and other OTAs.
            $table->string('external_mapping_id')->nullable()->comment('External system room type ID');
            $table->string('channel_manager_code')->nullable()->comment('Channel manager room type code');
            $table->string('rate_plan_code')->nullable()->comment('Default rate plan code for this room type');

            $table->timestamps();
            $table->softDeletes();

            // ── Indexes ────────────────────────────────────────────────────────
            $table->index('is_active');
            $table->index('external_mapping_id');
            $table->index('channel_manager_code');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('room_types');
    }
};
