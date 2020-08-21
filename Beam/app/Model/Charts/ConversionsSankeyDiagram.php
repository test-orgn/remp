<?php

namespace App\Model\Charts;

use Illuminate\Support\Collection;

class ConversionsSankeyDiagram
{
    const NODE_ARTICLES = 'articles';
    const NODE_TITLE = 'homepage + other';
    const NODE_PURCHASE = 'purchase';

    private $conversionSources;
    private $conversionSourceType;

    public $nodes = [];
    public $links = [];

    public function __construct(Collection $conversionSources, string $conversionSourceType)
    {
        $this->conversionSources = $conversionSources;
        $this->conversionSourceType = $conversionSourceType;

        $this->retrieveNodesAndLinks();
    }

    private function retrieveNodesAndLinks()
    {
        $conversionSourcesByMedium = $this->conversionSources->where('type', $this->conversionSourceType)->groupBy('referer_medium');
        $totalArticlesCount = $totalTitlesCount = 0;

        foreach ($conversionSourcesByMedium as $medium => $conversionSources) {
            $articlesCount = $conversionSources->filter(function ($conversionSource) {
                return !empty($conversionSource->pageview_article_external_id);
            })->count();
            $titlesCount = $conversionSources->count() - $articlesCount;

            $this->addNodesAndLinks($medium, self::NODE_ARTICLES, $articlesCount);
            $this->addNodesAndLinks($medium, self::NODE_TITLE, $titlesCount);
            $totalArticlesCount += $articlesCount;
            $totalTitlesCount += $titlesCount;
        }

        $this->addNodesAndLinks(self::NODE_ARTICLES, self::NODE_PURCHASE, $totalArticlesCount);
        $this->addNodesAndLinks(self::NODE_TITLE, self::NODE_PURCHASE, $totalTitlesCount);
    }

    private function addNodesAndLinks(string $source, string $target, int $connectionCount)
    {
        if (!$connectionCount) {
            return;
        }

        $this->addNode($source);
        $this->addNode($target);

        $this->links[] = [
            'source' => $source,
            'target' => $target,
            'value' => $connectionCount
        ];
    }

    private function addNode(string $nodeName)
    {
        if (!in_array($nodeName, array_column($this->nodes, 'name'))) {
            $this->nodes[] = ['name' => $nodeName];
        }
    }
}
