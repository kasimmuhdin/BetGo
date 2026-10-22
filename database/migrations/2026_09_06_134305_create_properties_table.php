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

            $table->foreignId('user_id')->constrained()->cascadeOnDelete();

            // Basic
            $table->string('title');
            $table->text('description')->nullable();

            $table->enum('property_type', [
                'apartment',
                'house',
                'villa',
                'office',
                'land',
                'commercial',
                'warehouse',
            ]);

            $table->enum('listing_type', ['rent', 'sale']);

            $table->decimal('price', 15, 2);
            $table->string('currency', 3)->default('ETB');
            $table->boolean('price_negotiable')->default(false);

            $table->unsignedTinyInteger('bedrooms')->nullable();
            $table->unsignedTinyInteger('bathrooms')->nullable();
            $table->unsignedTinyInteger('parking_spaces')->nullable();

            $table->enum('furnishing_status', ['unfurnished', 'semi_furnished', 'furnished'])
                ->nullable();

            $table->decimal('area', 10, 2)->nullable();
            $table->string('area_unit', 10)->default('sqm');

            // Location
            $table->string('city');
            $table->string('sub_city')->nullable();
            $table->string('woreda')->nullable();
            $table->string('neighborhood')->nullable();
            $table->text('address')->nullable();
            $table->decimal('latitude', 10, 7)->nullable();
            $table->decimal('longitude', 10, 7)->nullable();

            $table->json('details')->nullable();

            $table->enum('status', [
                'draft',
                'pending',
                'published',
                'archived',
                'rejected',
            ])->default('draft');

            $table->boolean('is_verified')->default(false);

            $table->timestamp('published_at')->nullable();
            $table->timestamp('expires_at')->nullable();

            $table->timestamps();

            $table->index(['status', 'listing_type']);
            $table->index(['city', 'sub_city']);
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
