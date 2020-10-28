<?php
namespace Remp\MailerModule\Segment;

use Remp\MailerModule\ActiveRow;

class Dummy implements ISegment
{
    const PROVIDER_ALIAS = 'dummy-segment';

    public function provider(): string
    {
        return static::PROVIDER_ALIAS;
    }

    public function list(): array
    {
        return [
            [
                'name' => 'Dummy segment',
                'provider' => static::PROVIDER_ALIAS,
                'code' => 'dummy-segment',
                'group' => [
                    'id' => 0,
                    'name' => 'dummy',
                    'sorting' => 1
                ]
            ],
        ];
    }

    public function users(array $segment): array
    {
        return [1,2];
    }
}
