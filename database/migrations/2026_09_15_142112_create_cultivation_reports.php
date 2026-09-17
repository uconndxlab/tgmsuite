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
        Schema::create('cultivation_reports', function (Blueprint $table) {
            $table->id();
            $table->foreignId('report_id')->constrained()->cascadeOnDelete();
            $table->text('hollow_yes_no')->nullable();
            $table->text('hollow_notes')->nullable();
            $table->text('solid_yes_no')->nullable();
            $table->text('solid_notes')->nullable();
            $table->text('slice_yes_no')->nullable();
            $table->text('slice_notes')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('cultivation_reports');
    }
};
