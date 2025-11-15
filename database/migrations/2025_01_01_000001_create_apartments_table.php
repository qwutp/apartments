<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('apartments', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('address');
            $table->decimal('price_per_night', 10, 2);
            $table->integer('rooms');
            $table->decimal('total_area', 8, 2);
            $table->decimal('kitchen_area', 8, 2)->nullable();
            $table->decimal('living_area', 8, 2)->nullable();
            $table->integer('floor')->nullable();
            $table->enum('balcony', ['none', 'balcony', 'loggia'])->default('none');
            $table->enum('bathroom', ['shared', 'private', 'multiple'])->default('private');
            $table->string('renovation')->nullable();
            $table->boolean('has_furniture')->default(true);
            $table->boolean('has_appliances')->default(true);
            $table->boolean('has_internet')->default(true);
            
            // Rental conditions
            $table->decimal('deposit', 10, 2)->nullable();
            $table->decimal('commission', 10, 2)->nullable();
            $table->boolean('meter_based')->default(false);
            $table->string('other_utilities')->nullable();
            
            // Rules
            $table->integer('max_guests');
            $table->boolean('allows_children')->default(true);
            $table->boolean('allows_pets')->default(false);
            $table->boolean('allows_smoking')->default(false);
            
            // Additional
            $table->text('description')->nullable();
            $table->decimal('rating', 3, 2)->nullable();
            $table->integer('review_count')->default(0);
            $table->string('latitude')->nullable();
            $table->string('longitude')->nullable();
            $table->foreignId('owner_id')->constrained('users')->onDelete('cascade');
            $table->enum('status', ['available', 'booked', 'occupied'])->default('available');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('apartments');
    }
};
