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
        Schema::create('services_requests', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('status_id')->constrained('statuses')->onDelete('cascade');
            $table->foreignUuid('service_id')->constrained()->onDelete('cascade');
            $table->foreignUuid('user_id')->nullable()->constrained()->onDelete('cascade');
            $table->string('full_name');
            $table->string('email');
            $table->string('phone_number');
            $table->string('address');
            $table->string('details')->nullable();
            $table->decimal('propose_price', 8, 2)->nullable();
            $table->decimal('negotiated_price', 8, 2)->nullable();
            $table->foreignUuid('validated_by')->nullable()->constrained('users')->onDelete('set null');
            $table->foreignUuid('rejected_by')->nullable()->constrained('users')->onDelete('set null');
            $table->softDeletes();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('services_requests');
    }
};
