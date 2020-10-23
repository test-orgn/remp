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
        'url_terms'
    ];

    /**
     * Text should return textual representation of the banner's main text in the cleanest possible form.
     * @return mixed
     */
    public function text()
    {
        return strip_tags("({$this->newsletter_id}) {$this->title} -- {$this->text}");
    }
}
