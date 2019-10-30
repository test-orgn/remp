<?php

use App\Model\Config\ConfigNames;
use App\Model\Config\Config;
use Illuminate\Database\Migrations\Migration;

class SeedConfigValues4 extends Migration
{
    public function up()
    {
        Config::firstOrCreate([
            'name' => ConfigNames::REFERER_MEDIUMS_SHOWN_AS_SINGLE_SOURCE,
            'display_name' => 'Referer mediums shown as single source',
            'description' => 'When displaying referer stats, user might not want to distinguish between sources of particular (referer) medium (e.g. notifications). Multiple values should be separated by coma.',
            'type' => 'string',
            'value' => null, // by default, nothing specified
        ]);
    }
}
