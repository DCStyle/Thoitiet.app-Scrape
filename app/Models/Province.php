<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Province extends Model
{
    protected $table = 'provinces';
    protected $primaryKey = 'code';
    public $incrementing = false;
    protected $keyType = 'string';
    public $timestamps = false;

    protected $fillable = [
        'code', 'name', 'name_en', 'full_name', 'full_name_en', 'code_name',
        'administrative_unit_id', 'administrative_region_id',
        'lat', 'lng'
    ];

    /**
     * Get all districts belonging to this province
     */
    public function districts()
    {
        return $this->hasMany(District::class, 'province_code', 'code');
    }

    /**
     * Get the coordinates for this province
     *
     * @return array
     */
    public function getCoordinates()
    {
        if ($this->lat && $this->lng) {
            return ['lat' => $this->lat, 'lng' => $this->lng];
        }

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
        return url($this->getSlug());
    }
}
