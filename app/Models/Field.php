<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Field extends Model
{
    protected $guarded = ['id'];

    public function users()
    {
        return $this->belongsToMany(User::class, 'field_user')
                    ->withPivot('permission_level')
                    ->withTimestamps();
    }

    public function reports()
    {
        return $this->hasMany(Report::class);
    }

    public function getSportsPlayedArrayAttribute(): array
    {
        return array_filter(array_map('trim', explode(',', $this->sports_played ?? '')));
    }

    public function getTurfgrassSpeciesPresentArrayAttribute(): array
    {
        return array_filter(array_map('trim', explode(',', $this->turfgrass_species_present ?? '')));
    }

    public function getSoilTextureArrayAttribute(): array
    {
        return array_filter(array_map('trim', explode(',', $this->soil_texture ?? '')));
    }

    public function getWaterSourceArrayAttribute(): array
    {
        return array_filter(array_map('trim', explode(',', $this->water_source ?? '')));
    }
}
