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
        Schema::create('fields', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('address')->nullable();
            $table->string('city')->nullable();
            $table->string('state')->nullable();
            $table->string('zip')->nullable();
            $table->string('multiple_sport_usage')->nullable();
            $table->text('sports_played')->nullable(); // csv / json
            $table->text('turfgrass_species_present')->nullable();
            $table->string('establishment_method')->nullable();
            $table->string('establishment_date')->nullable();
            $table->string('percent_renovated')->nullable();
            $table->string('renovation_date')->nullable();
            $table->string('renovation_type')->nullable();
            $table->text('soil_texture')->nullable();
            $table->string('soil_depth')->nullable();
            $table->string('soil_condition')->nullable();
            $table->string('shade_or_sun')->nullable();
            $table->string('percent_shade')->nullable();
            $table->string('color_rating')->nullable();
            $table->string('irrigation_system')->nullable();
            $table->text('water_source')->nullable();
            $table->string('irrigation_frequency')->nullable();
            $table->string('portable_system')->nullable();
            $table->string('wetting_agents')->nullable();
            $table->string('mowing_height')->nullable();
            $table->string('mowing_frequency')->nullable();
            $table->string('pgrs_used')->nullable();
            $table->string('mowing_method')->nullable();
            $table->text('description')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('fields');
    }
};
