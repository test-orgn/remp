<?php

namespace Remp\MailerModule\PageMeta;

interface TransportInterface
{
    public function getContent(string $url): ?string;
}
