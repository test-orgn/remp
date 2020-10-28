<?php

namespace Remp\MailerModule\Config;

class LocalConfig
{
    private $data;

    public function __construct(array $data = [])
    {
        $this->data = $data;
    }

    public function value(string $key)
    {
        if ($this->exists($key)) {
            return $this->data[$key];
        }
        return false;
    }

    public function exists(string $key): bool
    {
        return isset($this->data[$key]);
    }
}
