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
        Schema::create('pest_management_reports', function (Blueprint $table) {
            $table->id();
            $table->foreignId('report_id')->constrained()->cascadeOnDelete();
            $table->integer('broadleaf_dandelion')->nullable();
            $table->integer('broadleaf_dandelion_percent')->nullable();
            $table->integer('broadleaf_plantain')->nullable();
            $table->integer('broadleaf_plantain_percent')->nullable();
            $table->integer('narrowleaf_plantain')->nullable();
            $table->integer('narrowleaf_plantain_percent')->nullable();
            $table->integer('heal_all')->nullable();
            $table->integer('heal_all_percent')->nullable();
            $table->integer('common_chickweed')->nullable();
            $table->integer('common_chickweed_percent')->nullable();
            $table->integer('oxalis')->nullable();
            $table->integer('oxalis_percent')->nullable();
            $table->integer('spurge')->nullable();
            $table->integer('spurge_percent')->nullable();
            $table->integer('knotweed')->nullable();
            $table->integer('knotweed_percent')->nullable();
            $table->integer('ground_ivy')->nullable();
            $table->integer('ground_ivy_percent')->nullable();
            $table->integer('violet')->nullable();
            $table->integer('violet_percent')->nullable();
            $table->integer('mouse_ear_chickweed')->nullable();
            $table->integer('mouse_ear_chickweed_percent')->nullable();
            $table->integer('clover_white')->nullable();
            $table->integer('clover_white_percent')->nullable();
            $table->integer('speedwell')->nullable();
            $table->integer('speedwell_percent')->nullable();
            $table->text('other')->nullable();
            $table->integer('other_percent')->nullable();
            $table->text('broadleaf_control')->nullable();
            
            // Grasses
            $table->integer('crabgrass')->nullable();
            $table->integer('crabgrass_percent')->nullable();
            $table->text('crabgrass_control')->nullable();
            $table->integer('poa_annua')->nullable();
            $table->integer('poa_annua_percent')->nullable();
            $table->integer('quackgrass')->nullable();
            $table->integer('quackgrass_percent')->nullable();
            $table->integer('goosegrass')->nullable();
            $table->integer('goosegrass_percent')->nullable();
            $table->integer('poa_trivialis')->nullable();
            $table->integer('poa_trivialis_percent')->nullable();
            $table->integer('bentgrass')->nullable();
            $table->integer('bentgrass_percent')->nullable();
            $table->integer('tall_fescue')->nullable();
            $table->integer('tall_fescue_percent')->nullable();
            $table->integer('yellow_nutsedge')->nullable();
            $table->integer('yellow_nutsedge_percent')->nullable();
            $table->integer('orchardgrass')->nullable();
            $table->integer('orchardgrass_percent')->nullable();
            $table->text('other_grasses')->nullable();
            $table->integer('other_grasses_percent')->nullable();
            
            // Insects
            $table->integer('insects_grubs')->nullable();
            $table->text('insects_grubs_type')->nullable();
            $table->integer('insects_grubs_percent')->nullable();
            $table->integer('insects_sod_webworm')->nullable();
            $table->integer('insects_sod_webworm_percent')->nullable();
            $table->integer('insects_chinch_bug')->nullable();
            $table->integer('insects_chinch_bug_percent')->nullable();
            $table->integer('insects_billbug')->nullable();
            $table->integer('insects_billbug_percent')->nullable();
            $table->text('other_insects')->nullable();
            $table->integer('other_insects_percent')->nullable();
            $table->text('insects_control')->nullable();
            
            // Diseases
            $table->text('disease_present')->nullable();
            $table->integer('disease_tall_fescue')->nullable();
            $table->integer('disease_perennial_ryegrass')->nullable();
            $table->integer('disease_kentucky_bluegrass')->nullable();
            $table->integer('disease_fine_fescue')->nullable();
            $table->integer('disease_other')->nullable();
            $table->integer('disease_percent')->nullable();
            $table->text('disease_control')->nullable();
            
            $table->text('pest_comments')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('pest_management_reports');
    }
};
