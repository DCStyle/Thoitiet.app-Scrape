<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class District extends Model
{
    protected $table = 'districts';
    protected $primaryKey = 'code';
    public $incrementing = false;
    protected $keyType = 'string';
    public $timestamps = false;

    protected $fillable = [
        'code', 'name', 'name_en', 'full_name', 'full_name_en', 'code_name',
        'province_code', 'administrative_unit_id',
        'lat', 'lng'
    ];

    /**
     * Get the province that this district belongs to
     */
    public function province()
    {
        return $this->belongsTo(Province::class, 'province_code', 'code');
    }

    /**
     * Get all wards belonging to this district
     */
    public function wards()
    {
        return $this->hasMany(Ward::class, 'district_code', 'code');
    }

    /**
     * Get the coordinates for this district
     *
     * @return array
     */
    public function getCoordinates()
    {
        if ($this->lat && $this->lng) {
            return ['lat' => $this->lat, 'lng' => $this->lng];
        }

        // Default to Hanoi if coordinates not found
        return null;
    }

    /**
     * Get formatted slug for routing
     */
    public function getSlug()
    {
        // Replace underscores with hyphens for URLs
        return str_replace('_', '-', $this->code_name);
    }

    public function getUrl()
    {
        return url($this->province->getSlug() . '/' . $this->getSlug());
    }
}
