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
            'display_name' => 'Merge referer mediums into single source (comma separated list of referer mediums)',
            'description' => 'Each tracked pageview has associated referer_medium value (e.g. internal, external, social). When displaying referer stats (e.g. in article detail), you might want to utilize referer_medium value instead of real referer of the pageview. This means we won\'t distinguish between different referers having the same medium. For example, you might want to show all push_notifications as a single source in referer stats table, even when the actual referer has several different values.',
            'type' => 'string',
            'value' => null, // by default, nothing specified
        ]);
    }
}
