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
        'params_extra',
        'response_failure'
    ];

    public function getEndpointAttribute()
    {
        return config('newsletter_banners.endpoint');
    }

    public function getUseXhrAttribute()
    {
        return config('newsletter_banners.use_xhr');
    }

    public function getRequestMethodAttribute()
    {
        return config('newsletter_banners.request_method');
    }

    public function getRequestBodyAttribute()
    {
        return config('newsletter_banners.request_body');
    }

    public function getRequestHeadersAttribute()
    {
        return config('newsletter_banners.request_headers');
    }

    public function getParamsTrAttribute()
    {
        return config('newsletter_banners.params_tr');
    }

    public function getParamsExtraAttribute()
    {
        return config('newsletter_banners.params_extra');
    }

    public function getResponseFailureAttribute()
    {
        return config('newsletter_banners.response_failure');
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
