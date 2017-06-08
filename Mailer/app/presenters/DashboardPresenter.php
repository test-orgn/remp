<?php

namespace Remp\MailerModule\Presenters;

use Remp\MailerModule\Components\IQueueStatsFactory;

final class DashboardPresenter extends BasePresenter
{
    public function renderDefault()
    {

    }

    protected function createComponentQueueStats(IQueueStatsFactory $factory)
    {
        $templateStats = $factory->create();
        return $templateStats;
    }
}
