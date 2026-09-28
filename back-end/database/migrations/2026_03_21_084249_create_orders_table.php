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
        Schema::create('orders', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('first_name');
            $table->string('last_name');
            $table->string('email');
            $table->foreignUuid('status_id')->nullable()->constrained('statuses')->onDelete('set null');
            $table->foreignUuid('payment_status_id')->nullable()->constrained('statuses')->onDelete('set null');
            $table->foreignUuid('user_id')->nullable()->constrained('users')->onDelete('restrict');
            $table->string("order_number")->nullable()->unique();
            $table->foreignUuid('city_id')->nullable()->constrained('cities')->onDelete('set null');
            $table->foreignUuid('municipality_id')->nullable()->constrained('municipalities')->onDelete('set null');
            $table->string("address")->nullable();
            $table->string('payment_method');
            $table->string("phone_number");
            $table->decimal('total_amount', 15, 2);
            $table->foreignUuid('validated_by')->nullable()->constrained('users')->onDelete('set null');
            $table->foreignUuid('canceled_by')->nullable()->constrained('users')->onDelete('set null');
            $table->string('cancellation_reason')->nullable();
            $table->softDeletes();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('orders');
    }
};
