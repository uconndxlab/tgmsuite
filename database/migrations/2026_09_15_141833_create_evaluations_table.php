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
        Schema::create('evaluations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('report_id')->constrained()->cascadeOnDelete();
            $table->integer('turf_density')->nullable();
            $table->integer('smoothness_rating')->nullable();
            $table->integer('weeds_rating')->nullable();
            $table->integer('stones_at_surface')->nullable();
            $table->integer('depressions')->nullable();
            $table->integer('turf_rating')->nullable();
            $table->integer('surface_rating')->nullable();
            $table->integer('overall_rating')->nullable();
            $table->text('quality_comments')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('evaluations');
    }
};
