<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Ward extends Model
{
    protected $table = 'wards';
    protected $primaryKey = 'code';
    public $incrementing = false;
    protected $keyType = 'string';
    public $timestamps = false;

    protected $fillable = [
        'code', 'name', 'name_en', 'full_name', 'full_name_en', 'code_name',
        'district_code', 'administrative_unit_id',
        'lat', 'lng'
    ];

    /**
     * Get the district that this ward belongs to
     */
    public function district()
    {
        return $this->belongsTo(District::class, 'district_code', 'code');
    }

    /**
     * Get the province through the district relationship
     */
    public function province()
    {
        return $this->district ? $this->district->province : null;
    }

    /**
     * Get the coordinates for this ward
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
        if ($this->district && $this->district->province) {
            return url(
                $this->district->province->getSlug() . '/' .
                $this->district->getSlug() . '/' .
                $this->getSlug()
            );
        }

        return '#';
    }
}
