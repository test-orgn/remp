<?php

namespace Remp\MailerModule\PageMeta;

interface ContentInterface
{
    public function fetchUrlMeta(string $url): ?Meta;
}
