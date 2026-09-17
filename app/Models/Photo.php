<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Photo extends Model
{
    protected $guarded = ['id'];

    public function report()
    {
        return $this->belongsTo(Report::class);
    }

    public function associatedReport()
    {
        return $this->belongsTo(Report::class, 'associated_report_id');
    }
}
