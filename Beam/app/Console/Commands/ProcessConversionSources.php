<?php

namespace App\Console\Commands;

use App\Conversion;
use App\Model\ConversionCommerceEvent;
use App\Model\ConversionSource;
use Illuminate\Console\Command;
use Illuminate\Database\Eloquent\Collection;
use Remp\Journal\JournalContract;
use Remp\Journal\JournalException;
use Remp\Journal\ListRequest;

class ProcessConversionSources extends Command
{
    const COMMAND = 'conversions:process-sources';

    private $journal;

    protected $signature = self::COMMAND . ' {--conversion_id=}';

    protected $description = 'Retrieve visit sources that lead to conversion';

    public function __construct(JournalContract $journal)
    {
        parent::__construct();

        $this->journal = $journal;
    }

    public function handle()
    {
        $this->line('Started processing of conversion sources');
        $conversion_id = $this->option('conversion_id') ?? null;

        try {
            if ($conversion_id) {
                $conversion = Conversion::find($conversion_id);
                if (!$conversion) {
                    $this->error("Conversion with ID $conversion_id not found.");
                    return;
                }

                if (!$conversion->events_aggregated) {
                    $this->warn("Conversion with ID $conversion_id needs to be aggregated prior to running of this command.");
                    return;
                }

                if ($conversion->conversionSources()->count()) {
                    $this->info("Sources for conversion with ID $conversion_id has already been processed.");
                    return;
                }

                $this->processConversionSources($conversion);
            } else {
                foreach ($this->getAggregatedConversionsWithoutSource() as $conversion) {
                    $this->processConversionSources($conversion);
                }
            }
        } catch (JournalException $exception) {
            $this->error($exception->getMessage());
        }

        $this->line(' <info>Done!</info>');
    }

    private function getAggregatedConversionsWithoutSource(): Collection
    {
        return Conversion::where('events_aggregated', true)
            ->with('conversionSources')
            ->get()
            ->filter(function ($conversion) {
                return $conversion->conversionSources->count() === 0;
            });
    }

    private function processConversionSources(Conversion $conversion)
    {
        $this->line("Processing sources for conversion <info>#{$conversion->id}</info>");

        $paymentEvent = $conversion->commerceEvents()->where('step', 'payment')->latest('time')->first();

        if (!$paymentEvent) {
            $this->warn("No payment event found in DB for conversion with ID $conversion->id, skipping...");
            return;
        }

        if (!$browser_id = $this->getConversionBrowserId($conversion, $paymentEvent)) {
            return;
        }

        if (!$conversionSessionId = $this->getConversionSessionId($browser_id, $paymentEvent)) {
            return;
        }

        $conversionSources = $this->getConversionSources($conversionSessionId, $paymentEvent, $conversion);
        foreach ($conversionSources as $conversionSource) {
            $conversionSource->save();
        }
    }

    private function getConversionBrowserId(Conversion $conversion, ConversionCommerceEvent $paymentEvent)
    {
        $from = (clone $paymentEvent->time)->subSecond();
        $to = (clone $paymentEvent->time)->addSecond();

        $paymentListRequest = ListRequest::from('commerce')
            ->setTime($from, $to)
            ->addFilter('user_id', $conversion->user_id)
            ->addFilter('step', 'payment')
            ->addGroup('browser_id');

        $paymentJournalEvent = $this->journal->list($paymentListRequest);
        if (empty($paymentJournalEvent)) {
            $this->warn("No payment event found in journal for conversion with ID $conversion->id, skipping...");
            return false;
        }
        if (empty($paymentJournalEvent[0]->tags->browser_id)) {
            $this->warn("No identifiable browser found in journal for conversion with ID $conversion->id, skipping...");
            return false;
        }

        return $paymentJournalEvent[0]->tags->browser_id;
    }

    private function getConversionSessionId(string $browser_id, ConversionCommerceEvent $paymentEvent)
    {
        $from = (clone $paymentEvent->time)->subDay();
        $to = (clone $paymentEvent->time);

        $pageViewsListRequest = ListRequest::from('pageviews')
            ->setTime($from, $to)
            ->addFilter('browser_id', $browser_id);

        $pageViewsJournalEvents = $this->journal->list($pageViewsListRequest);
        if (empty($pageViewsJournalEvents)) {
            $this->warn("No pageview found in journal for conversion with ID $paymentEvent->conversion_id, skipping...");
            return false;
        }

        $pageViews = collect($pageViewsJournalEvents[0]->pageviews);
        $lastPageView = $pageViews->where('system.time', $pageViews->max('system.time'))->first();

        return $lastPageView->user->remp_session_id;
    }

    /**
     * @param string $conversionSessionId
     * @param ConversionCommerceEvent $paymentEvent
     * @param Conversion $conversion
     * @return ConversionSource[]
     */
    private function getConversionSources(
        string $conversionSessionId,
        ConversionCommerceEvent $paymentEvent,
        Conversion $conversion
    ) {
        $from = (clone $paymentEvent->time)->subDay();
        $to = (clone $paymentEvent->time);

        $latestPageViewsListRequest = ListRequest::from('pageviews')
            ->setTime($from, $to)
            ->addGroup('derived_referer_host_with_path', 'derived_referer_medium', 'derived_referer_source')
            ->addFilter('remp_session_id', $conversionSessionId);

        $conversionPageViews = collect($this->journal->list($latestPageViewsListRequest));
        //filter out the pageviews that contain article url (e.g. refresh or source of checkout pageview (if tracked))
        $article_url = $conversion->article->url;
        $conversionPageViews = $conversionPageViews->filter(function ($conversionPageView) use ($article_url) {
            $referer_host_with_path = $conversionPageView->tags->derived_referer_host_with_path;
            $referer_source = $conversionPageView->tags->derived_referer_source;

            return ($referer_host_with_path !== $article_url && $referer_source !== $article_url);
        });

        $firstConversionPageviewTags = $conversionPageViews->where(
            'pageviews.*.system.time',
            $conversionPageViews->min('pageviews.*.system.time')
        )->first()->tags;

        $lastConversionPageviewTags = $conversionPageViews->where(
            'pageviews.*.system.time',
            $conversionPageViews->max('pageviews.*.system.time')
        )->first()->tags;

        $conversionSources[] = $this->createConversionSourceModel($firstConversionPageviewTags, $conversion, 'first');
        $conversionSources[] = $this->createConversionSourceModel($lastConversionPageviewTags, $conversion, 'last');

        return $conversionSources;
    }

    private function createConversionSourceModel(object $conversionPageViewTags, Conversion $conversion, string $type)
    {
        $conversionSource = new ConversionSource();

        $conversionSource->type = $type;
        $conversionSource->referer_medium = $conversionPageViewTags->derived_referer_medium;
        $conversionSource->referer_source = empty($conversionPageViewTags->derived_referer_source) ? null : $conversionPageViewTags->derived_referer_source;
        $conversionSource->referer_host_with_path = empty($conversionPageViewTags->derived_referer_host_with_path) ? null : $conversionPageViewTags->derived_referer_host_with_path;
        $conversionSource->conversion()->associate($conversion);

        return $conversionSource;
    }
}
