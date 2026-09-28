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
        // product_variants
        Schema::create('product_variants', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('status_id')->nullable()->constrained('statuses')->onDelete('set null');
            $table->foreignUuid('product_id')->constrained('products')->onDelete('cascade'); // NOT NULL
            $table->integer('stock_quantity')->default(0); // toujours connu
            $table->string('sku')->unique()->nullable();
            $table->decimal('price', 15, 2)->nullable(); // NULL = hérite du produit parent
            $table->foreignUuid('created_by')->nullable()->constrained('users')->onDelete('set null');
            $table->foreignUuid('updated_by')->nullable()->constrained('users')->onDelete('set null');
            $table->foreignUuid('deleted_by')->nullable()->constrained('users')->onDelete('set null');
            $table->timestamps();
            $table->softDeletes();
        });

    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
       Schema::dropIfExists('product_variants');
    }
};
