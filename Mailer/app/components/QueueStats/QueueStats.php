<?php

namespace Remp\MailerModule\Components;

use Nette\Application\UI\Control;
use Remp\MailerModule\Job\Queue;
use Remp\MailerModule\Repository\BatchesRepository;

class QueueStats extends Control
{
    /** @var BatchesRepository */
    private $batchesRepository;

    /** @var Queue */
    private $queue;

    public function __construct(
        BatchesRepository $batchesRepository,
        Queue $queue
    ) {
        $this->batchesRepository = $batchesRepository;
        $this->queue = $queue;

        parent::__construct();
    }

    public function render()
    {
        $stats = [];
        $batches = $this->batchesRepository->getActiveBatches();

        foreach ($batches as $batch) {
            $totalCount = $batch->max_emails;
            $count = $this->queue->getTasksCount($batch->id);

            $stats[$batch->job_id][] = [
                'totalCount' => $totalCount,
                'count' => $count,
                'percent' => round($count / $totalCount * 100),
                'active' => $this->queue->isQueueActive($batch->id),
            ];
        }

        $this->template->stats = $stats;

        $this->template->setFile(__DIR__ . '/queue_stats.latte');
        $this->template->render();
    }
}
