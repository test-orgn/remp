<?php

namespace App\Model;

use App\Article;
use App\Conversion;
use Illuminate\Database\Eloquent\Model;

class ConversionSource extends Model
{
    const TYPE_FIRST = 'first';
    const TYPE_LAST = 'last';

    protected $fillable = [
        'conversion_id',
        'type',
        'referer_medium',
        'referer_source',
        'referer_host_with_path',
        'pageview_article_external_id',
    ];

    protected $dates = [
        'created_at',
        'updated_at'
    ];

    public function conversion()
    {
        return $this->belongsTo(Conversion::class);
    }

    public function article()
    {
        return $this->belongsTo(Article::class, 'pageview_url', 'url');
    }
}
