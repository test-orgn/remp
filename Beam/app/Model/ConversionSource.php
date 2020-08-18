<?php

namespace App\Model;

use App\Conversion;
use Illuminate\Database\Eloquent\Model;

class ConversionSource extends Model
{
    const PAGEVIEWTYPE_ARTICLE = 'article';
    const PAGEVIEWTYPE_TITLE_AND_OTHER = 'title and other';

    protected $fillable = [
        'conversion_id',
        'type',
        'referer_medium',
        'referer_source',
        'referer_host_with_path',
        'pageview_url',
        'pageview_type'
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
