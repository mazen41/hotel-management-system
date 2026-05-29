<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('reservations', function (Blueprint $table) {
            $table->id();
            $table->string('confirmation_number')->unique();
            $table->foreignId('guest_id')->constrained('guests')->onDelete('restrict');
            $table->foreignId('room_id')->constrained('rooms')->onDelete('restrict');
            $table->foreignId('room_type_id')->constrained('room_types')->onDelete('restrict');

            // Dates
            $table->date('check_in');
            $table->date('check_out');
            $table->unsignedInteger('nights')->default(1);

            // Guests
            $table->unsignedInteger('adults')->default(1);
            $table->unsignedInteger('children')->default(0);

            // Pricing
            $table->decimal('rate_per_night', 10, 2);
            $table->decimal('subtotal', 10, 2);
            $table->decimal('taxes', 10, 2)->default(0);
            $table->decimal('total_amount', 10, 2);
            $table->decimal('amount_paid', 10, 2)->default(0);
            $table->decimal('balance_due', 10, 2)->default(0);

            // Status
            $table->enum('status', [
                'pending',
                'confirmed',
                'checked_in',
                'checked_out',
                'cancelled',
                'no_show',
            ])->default('pending');

            $table->enum('payment_status', [
                'unpaid',
                'partial',
                'paid',
                'refunded',
            ])->default('unpaid');

            // Source
            $table->string('source')->default('direct'); // direct, booking_com, expedia, airbnb, nobeds, walk_in
            $table->string('source_reservation_id')->nullable(); // external booking ID (e.g. Booking.com booking number)

            // NoBeds / Channel Manager fields
            $table->string('channel_reservation_id')->nullable();
            $table->string('channel_name')->nullable();
            $table->timestamp('channel_synced_at')->nullable();

            $table->text('special_requests')->nullable();
            $table->text('internal_notes')->nullable();
            $table->timestamp('cancelled_at')->nullable();
            $table->string('cancellation_reason')->nullable();

            $table->timestamps();
            $table->softDeletes();

            $table->index('confirmation_number');
            $table->index('check_in');
            $table->index('check_out');
            $table->index('status');
            $table->index('source');
            $table->index('channel_reservation_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('reservations');
    }
};
