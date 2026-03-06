<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            //
            $table->string("order_number")->nullable()->unique()->after('id');
            $table->string('first_name')->nullable()->after('user_id');
            $table->string('last_name')->nullable()->after('first_name');
            $table->foreignUuid('city_id')->nullable()->constrained('cities')->onDelete('set null')->after('phone_number');
            $table->foreignUuid('municipality_id')->nullable()->constrained('municipalities')->onDelete('set null')->after('city_id');
            $table->string("address")->nullable()->after('municipality_id');
            $table->string('payment_method')->nullable()->after('address');
            $table->string("phone_number")->nullable()->after('total_amount');
            $table->softDeletes()->after('updated_at');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            //
        });
    }
};
