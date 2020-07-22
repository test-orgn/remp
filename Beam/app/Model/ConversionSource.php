<?php

namespace App\Model;

use App\Conversion;
use Illuminate\Database\Eloquent\Model;

class ConversionSource extends Model
{
    protected $fillable = [
        'conversion_id',
        'type',
        'referer_medium',
        'referer_source',
        'referer_host_with_path'
    ];

    protected $dates = [
        'created_at',
        'updated_at'
    ];

    public function conversion()
    {
        return $this->belongsTo(Conversion::class);
    }
}
