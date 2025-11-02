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
        Schema::create('properties', function (Blueprint $table) {
            $table->id();

            // Basic property information
            $table->string('title')->nullable();
            $table->text('description')->nullable();
            $table->string('address')->nullable();
            $table->string('city')->nullable();
            $table->string('state')->nullable();
            $table->string('zip_code')->nullable();
            $table->decimal('latitude', 10, 8)->nullable();
            $table->decimal('longitude', 11, 8)->nullable();

            // Core property metrics you requested
            $table->integer('square_feet')->unsigned()->nullable();
            $table->integer('bedrooms')->unsigned()->nullable();
            $table->integer('bathrooms')->unsigned()->nullable();
            $table->integer('floors')->unsigned()->nullable();

            // Financial information
            $table->decimal('price', 12, 2)->nullable();
            $table->decimal('price_per_sqft', 8, 2)->nullable();
            $table->decimal('monthly_rent', 10, 2)->nullable();
            $table->decimal('property_taxes', 10, 2)->nullable();

            // Property details for scalability
            $table->string('property_type')->nullable(); // house, condo, apartment, etc.
            $table->string('status')->default('available'); // available, sold, rented, etc.
            $table->year('year_built')->nullable();
            $table->decimal('lot_size', 10, 2)->nullable();
            $table->integer('garage_spaces')->unsigned()->nullable();
            $table->boolean('has_basement')->default(false);
            $table->boolean('has_pool')->default(false);
            $table->boolean('has_garden')->default(false);

            // JSON field for additional flexible attributes
            $table->json('features')->nullable(); // For amenities, utilities, etc.
            $table->json('metadata')->nullable(); // For any additional custom data

            // Foreign key for potential user ownership
            $table->foreignId('user_id')->nullable()->constrained()->onDelete('set null');

            $table->timestamps();

            // Indexes for performance
            $table->index(['city', 'state']);
            $table->index(['property_type', 'status']);
            $table->index(['price', 'square_feet']);
            $table->index(['bedrooms', 'bathrooms']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('properties');
    }
};
