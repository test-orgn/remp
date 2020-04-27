<?php

namespace App\Console\Commands;

use Carbon\Carbon;
use GuzzleHttp\Client;
use GuzzleHttp\Exception\ClientException;
use Illuminate\Console\Command;

class ElasticDataRetention extends Command
{
    const COMMAND = 'service:elastic-data-retention';

    protected $signature = self::COMMAND . ' {--host=} {--match-index=} {--date=} {--user=}';

    protected $description = 'Data retention tries to find index based on match-index and date options and removes it';

    public function handle()
    {
        if (!$this->input->getOption('host')) {
            $this->line('<error>ERROR</error> You need to provide <info>--host</info> option with address to your Elastic instance (e.g. <info>--host=http://localhost:9200</info>)');
            return;
        }
        if (!$this->input->getOption('match-index')) {
            $this->line('<error>ERROR</error> You need to provide <info>--match-index</info> option with name of the index you want to cleanup (e.g. <info>--write-alias=pageviews_write</info>)');
            return;
        }
        if (!$this->input->getOption('date')) {
            $this->line('<error>ERROR</error> You need to provide <info>--date</info> option with date that will be searched within index name (e.g. <info>--date="90 days ago"</info>)');
            return;
        }

        $date = new Carbon($this->input->getOption('date'));
        $client = new Client([
            'base_uri' => $this->input->getOption('host'),
        ]);

        $targetIndices = sprintf(
            "/%s*%s*",
            $this->input->getOption('match-index'),
            $date->format('Y.m.d')
        );

        $this->line(sprintf(
            "Executing data retention for <info>%s%s</info>:",
            $this->input->getOption('host'),
            $targetIndices
        ));

        $options = [];
        if ($this->input->getOption('user')) {
            [$user, $pass] = explode(':', $this->input->getOption('user'), 2);
            $options = [
                'auth' => [$user, $pass]
            ];
        }

        // execute index delete; https://www.elastic.co/guide/en/elasticsearch/reference/current/indices-delete-index.html
        try {
            $client->delete($targetIndices, $options);
        } catch (ClientException $e) {
            $body = json_decode($e->getResponse()->getBody());
            dump($body);
            return;
        }

        $this->line('  * Done.');
    }
}
