<?php

namespace Remp\MailerModule\Components;

use Nette\Application\UI\Control;
use Remp\MailerModule\Job\Queue;

class QueueStats extends Control
{
    /** @var Queue */
    private $queue;

    public function __construct(Queue $queue)
    {
        $this->queue = $queue;
        parent::__construct();
    }

    public function render()
    {
        $stats = [];

        // get all jobs
        $jobs = [];

        foreach ($jobs as $job) {
            $stats[$job] = [
                'totalCount' => 1,
                'count' => $this->queue->getTasksCount($job),
                'status' => $this->queue->isQueueActive($job),
            ];
        }

        $this->template->stats = $stats;

        $this->template->setFile(__DIR__ . '/queue_stats.latte');
        $this->template->render();
    }
}
