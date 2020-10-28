<?php

namespace Remp\MailerModule\Filters;

class YesNoFilter
{
    public function process(int $string): string
    {
        return (boolean)$string ? 'Yes' : 'No';
    }
}
