<?php

namespace Remp\MailerModule\Components;

interface IQueueStatsFactory
{
    /** @return QueueStats */
    public function create();
}
