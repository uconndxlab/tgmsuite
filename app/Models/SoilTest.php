<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SoilTest extends Model
{
    protected $table = 'soil_test';

    protected $guarded = ['id'];

    public function report()
    {
        return $this->belongsTo(Report::class);
    }
}
