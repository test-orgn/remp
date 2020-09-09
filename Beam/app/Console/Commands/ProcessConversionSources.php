<?php

namespace App\Console\Commands;

use App\Conversion;
use App\Model\ConversionCommerceEvent;
use App\Model\ConversionSource;
use Illuminate\Console\Command;
use Illuminate\Support\Collection;
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

        if (!$paymentEvent->browser_id) {
            $this->warn("No browser_id attached to payment commerce event for conversion with ID $conversion->id, skipping...");
            return;
        }

        if (!$conversionSessionId = $this->getConversionSessionId($paymentEvent)) {
            return;
        }

        $conversionSources = $this->getConversionSources($conversionSessionId, $paymentEvent, $conversion);
        foreach ($conversionSources as $conversionSource) {
            $conversionSource->save();
        }
    }

    private function getConversionSessionId(ConversionCommerceEvent $paymentEvent)
    {
        $from = (clone $paymentEvent->time)->subDay();
        $to = (clone $paymentEvent->time);
        $browserId = $paymentEvent->browser_id;

        $pageViewsListRequest = ListRequest::from('pageviews')
            ->setTime($from, $to)
            ->addFilter('browser_id', $browserId);

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

        $conversionPageViewsGroups = collect($this->journal->list($latestPageViewsListRequest));
        //filter out the pageviews that contain article url (e.g. refresh or source of checkout pageview (if tracked))
        $articleUrl = $conversion->article->url;
        $conversionPageViewsGroups = $conversionPageViewsGroups->filter(function ($conversionPageView) use ($articleUrl) {
            $refererHostWithPath = $conversionPageView->tags->derived_referer_host_with_path;
            $refererSource = $conversionPageView->tags->derived_referer_source;

            return ($refererHostWithPath !== $articleUrl && $refererSource !== $articleUrl);
        });

        $firstConversionPageViewTime = $conversionPageViewsGroups->min(function ($value) {
            return min(array_pluck($value->pageviews, 'system.time'));
        });
        $lastConversionPageViewTime = $conversionPageViewsGroups->max(function ($value) {
            return max(array_pluck($value->pageviews, 'system.time'));
        });

        $firstConversionPageViewsGroup = $this->getPageViewsGroupByTime($conversionPageViewsGroups, $firstConversionPageViewTime);
        $lastConversionPageViewsGroup = $this->getPageViewsGroupByTime($conversionPageViewsGroups, $lastConversionPageViewTime);

        $firstConversionPageView = $this->getPageViewByTime($firstConversionPageViewsGroup, $firstConversionPageViewTime);
        $lastConversionPageView = $this->getPageViewByTime($lastConversionPageViewsGroup, $lastConversionPageViewTime);

        $conversionSources[] = $this->createConversionSourceModel($firstConversionPageViewsGroup->tags, $firstConversionPageView, $conversion, 'first');
        $conversionSources[] = $this->createConversionSourceModel($lastConversionPageViewsGroup->tags, $lastConversionPageView, $conversion, 'last');

        return $conversionSources;
    }

    private function createConversionSourceModel(object $conversionPageViewTags, object $pageView, Conversion $conversion, string $type)
    {
        $conversionSource = new ConversionSource();

        $conversionSource->type = $type;
        $conversionSource->referer_medium = $conversionPageViewTags->derived_referer_medium;
        $conversionSource->referer_source = empty($conversionPageViewTags->derived_referer_source) ? null : $conversionPageViewTags->derived_referer_source;
        $conversionSource->referer_host_with_path = empty($conversionPageViewTags->derived_referer_host_with_path) ? null : $conversionPageViewTags->derived_referer_host_with_path;
        $conversionSource->pageview_article_external_id = property_exists($pageView, 'article') ? $pageView->article->id : null;
        $conversionSource->conversion()->associate($conversion);

        return $conversionSource;
    }

    private function getPageViewsGroupByTime(Collection $pageViewsGroups, string $pageViewTime)
    {
        $pageViewsGroup = $pageViewsGroups->filter(function ($conversionPageViewsGroup) use ($pageViewTime) {
            return in_array($pageViewTime, array_pluck($conversionPageViewsGroup->pageviews, 'system.time'));
        })->first();

        return $pageViewsGroup;
    }

    private function getPageViewByTime($pageViewsGroups, string $pageViewTime)
    {
        return collect($pageViewsGroups->pageviews)->where('system.time', $pageViewTime)->first();
    }
}
