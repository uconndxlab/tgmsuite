<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ColorReport extends Model
{
    protected $guarded = ['id'];

    public function report()
    {
        return $this->belongsTo(Report::class);
    }

    public function getColorLabelAttribute(): string
    {
        return match ((string) $this->color_option) {
            'TD' => 'Turf Dormant',
            '1' => '1 - Yellow Green',
            '2' => '2 - Light Green',
            '3' => '3 - Med/Light Green',
            '4' => '4 - Medium Green',
            '5' => '5 - Dark Green',
            default => (string) ($this->color_option ?? ''),
        };
    }

    public function getColorAttribute(): string
    {
        return $this->color_label;
    }
}
