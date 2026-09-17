<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Models\Field;
use App\Models\User;
use App\Models\Evaluation;
use App\Models\FertilizationReport;
use App\Models\Photo;
use App\Models\ColorReport;
use App\Models\TopdressingReport;
use App\Models\OverseedReport;
use App\Models\CultivationReport;
use App\Models\PestManagementReport;
use App\Models\ThatchAccumulationReport;
use App\Models\SoilTest;

class Report extends Model
{
    public $timestamps = false;

    protected $fillable = ['field_id', 'evaluator_id', 'evaluation_date', 'type'];

    public function field()
    {
        return $this->belongsTo(Field::class);
    }

    public function evaluator()
    {
        return $this->belongsTo(User::class, 'evaluator_id');
    }

    public function associatedPhotos()
    {
        return $this->hasMany(Photo::class, 'associated_report_id');
    }

    public function evaluation()
    {
        return $this->hasOne(Evaluation::class);
    }

    public function fertilization()
    {
        return $this->hasOne(FertilizationReport::class);
    }

    public function photo()
    {
        return $this->hasOne(Photo::class);
    }

    public function color()
    {
        return $this->hasOne(ColorReport::class);
    }

    public function topdressing()
    {
        return $this->hasOne(TopdressingReport::class);
    }

    public function overseeding()
    {
        return $this->hasOne(OverseedReport::class);
    }

    public function cultivation()
    {
        return $this->hasOne(CultivationReport::class);
    }

    public function pest()
    {
        return $this->hasOne(PestManagementReport::class);
    }

    public function thatchAccumulation()
    {
        return $this->hasOne(ThatchAccumulationReport::class);
    }

    public function soilTest()
    {
        return $this->hasOne(SoilTest::class);
    }

    public function getDetailsAttribute()
    {
        return match ($this->type) {
            'evaluation' => $this->evaluation,
            'fertilization' => $this->fertilization,
            'photo' => $this->photo,
            'color' => $this->color,
            'topdressing' => $this->topdressing,
            'overseeding' => $this->overseeding,
            'cultivation' => $this->cultivation,
            'pest' => $this->pest,
            'thatch_accumulation' => $this->thatchAccumulation,
            'soil_test' => $this->soilTest,
            default => null,
        };
    }

    public function scopeWithDetails($query)
    {
        return $query->with([
            'evaluator',
            'evaluation',
            'fertilization',
            'photo',
            'color',
            'topdressing',
            'overseeding',
            'cultivation',
            'pest',
            'thatchAccumulation',
            'soilTest',
        ]);
    }

    public function details()
    {
        return match ($this->type) {
            'evaluation' => $this->hasOne(Evaluation::class),
            'fertilization' => $this->hasOne(FertilizationReport::class),
            'photo' => $this->hasOne(Photo::class),
            'color' => $this->hasOne(ColorReport::class),
            'topdressing' => $this->hasOne(TopdressingReport::class),
            'overseeding' => $this->hasOne(OverseedReport::class),
            'cultivation' => $this->hasOne(CultivationReport::class),
            'pest' => $this->hasOne(PestManagementReport::class),
            'thatch_accumulation' => $this->hasOne(ThatchAccumulationReport::class),
            'soil_test' => $this->hasOne(SoilTest::class),
            default => $this->hasOne(Evaluation::class),
        };
    }
}
