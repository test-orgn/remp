<?php

namespace App\Console\Commands;

use App\Conversion;
use Carbon\Carbon;
use DateTime;
use Illuminate\Console\Command;
use Remp\Journal\JournalContract;
use Remp\Journal\ListRequest;

class DownloadConversions extends Command
{
    const COMMAND = 'conversions:download';

    protected $signature = self::COMMAND . ' {--rewrite} {--days=}';

    protected $description = 'Download conversions from Segments api';

    private $journal;

    public function __construct(JournalContract $journal)
    {
        parent::__construct();

        $this->journal = $journal;
    }

    public function handle()
    {
        $days = (int)$this->option('days');
        $threshold = Carbon::now()->subDays($days);

        $r = ListRequest::from('commerce')
            ->setTimeAfter($threshold)
            ->addFilter('step', 'purchase');

        $events = $this->journal->list($r);

        $events = collect($events[0]->commerces);

//        dump($events[0]->commerces);
//        die();

        foreach ($events as $event) {
            $conversion = Conversion::where('transaction_id', $event->purchase->transaction_id)->first();

            if ($this->option('rewrite') && $conversion) {
                $conversion->update([
//                    'article_id' =>
//                    'user_id' =>
                    'amount' => $event->purchase->revenue->amount,
                    'currency' => $event->purchase->revenue->currency,
                    'paid_at' => new DateTime($event->system->time),
                    'transaction_id' => $event->purchase->transaction_id,
                    'events_aggregated' => 0,
                    'created_at' => new DateTime(),
                    'updated_at' => new DateTime(),
                ]);
            } elseif (!$conversion) {
                Conversion::create([
//                    'article_id' =>
//                    'user_id' =>
                    'amount' => $event->purchase->revenue->amount,
                    'currency' => $event->purchase->revenue->currency,
                    'paid_at' => new DateTime($event->system->time),
                    'transaction_id' => $event->purchase->transaction_id,
                    'events_aggregated' => 0,
                    'created_at' => new DateTime(),
                    'updated_at' => new DateTime(),
                ]);
            }
        }
    }
}
