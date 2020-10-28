<?php

namespace App;

class NewsletterRectangleTemplate extends AbstractTemplate
{
    protected $fillable = [
        'newsletter_id',
        'btn_submit',
        'title',
        'text',
        'success',
        'failure',
        'terms',
        'url_terms',
        'text_color',
        'background_color',
        'button_background_color',
        'button_text_color',
        'width',
        'height'
    ];

    protected $appends = [
        'endpoint',
        'use_xhr',
        'request_method',
        'request_body',
        'request_headers',
        'params_tr',
        'params_extra'
    ];

    public function getEndpointAttribute()
    {
        return config('newsletterBanners.endpoint');
    }

    public function getUseXhrAttribute()
    {
        return config('newsletterBanners.use_xhr');
    }

    public function getRequestMethodAttribute()
    {
        return config('newsletterBanners.request_method');
    }

    public function getRequestBodyAttribute()
    {
        return config('newsletterBanners.request_body');
    }

    public function getRequestHeadersAttribute()
    {
        return config('newsletterBanners.request_headers');
    }

    public function getParamsTrAttribute()
    {
        return config('newsletterBanners.params_tr');
    }

    public function getParamsExtraAttribute()
    {
        return config('newsletterBanners.params_extra');
    }

    /**
     * Text should return textual representation of the banner's main text in the cleanest possible form.
     * @return mixed
     */
    public function text()
    {
        return strip_tags("({$this->newsletter_id}) {$this->title} -- {$this->text}");
    }
}
