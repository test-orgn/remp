<?php

namespace Remp\MailerModule\Job;

class InMemoryMailCache implements MailCache
{
    private $storage = [];
    private $queueScores = [];

    public function addJob($userId, $email, $templateCode, $queueId, $context, $params = []): bool
    {
        $job = json_encode([
            'userId' => $userId,
            'email' => $email,
            'templateCode' => $templateCode,
            'context' => $context,
            'params' => $params
        ]);

        if ($this->jobExists($job, $queueId)) {
            return false;
        }

        $this->storage[$queueId][] = $job;
        return true;
    }

    public function getJob($queueId)
    {
        if (!isset($this->storage[$queueId])) {
            return null;
        }

        return array_pop($this->storage[$queueId]);
    }

    public function getJobs($queueId, $count = 1): array
    {
        if (!isset($this->storage[$queueId])) {
            return [];
        }

        $items = [];

        for ($i = 1; $i <= $count; $i++) {
            $items[] = array_pop($this->storage[$queueId]);
        }

        return $items;
    }

    public function hasJobs($queueId)
    {
        return isset($this->storage[$queueId]) && count($this->storage[$queueId]) > 0;
    }

    public function jobExists($job, $queueId)
    {
        if (!isset($this->storage[$queueId])){
            return false;
        }

        foreach ($this->storage[$queueId] as $storedJob) {
            if ($storedJob === $job) {
                return true;
            }
        }
        return false;
    }

    public function removeQueue($queueId)
    {
        $this->storage[$queueId] = [];
        unset($this->queueScores[$queueId]);
    }

    public function pauseQueue($queueId)
    {
        $this->queueScores[$queueId] = 0;
    }

    public function restartQueue($queueId, $priority)
    {
        $this->queueScores[$queueId] = $priority;
    }

    public function isQueueActive($queueId)
    {
        return isset($this->queueScores[$queueId]) && $this->queueScores[$queueId] > 0;
    }

    public function isQueueTopPriority($queueId)
    {
        if (!isset($this->queueScores[$queueId])) {
            return false;
        }

        $queuePriority = $this->queueScores[$queueId];

        $maxPriority = -1;
        $maxQueueId = null;

        foreach ($this->queueScores as $currentQueueId => $priority) {
            if ($priority > $maxPriority) {
                $maxPriority = $priority;
                $maxQueueId = $currentQueueId;
            }
        }

        return $queuePriority == $maxPriority;
    }
}
