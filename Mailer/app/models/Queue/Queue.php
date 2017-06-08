<?php

namespace Remp\MailerModule\Job;

use Predis\Client;

class Queue
{
    const REDIS_KEY = 'queue-';
    const REDIS_PAUSED_QUEUES_KEY = 'paused-queues';

    /** @var Client */
    private $redis;

    private $host;

    private $port;

    private $db;

    public function __construct($host = '127.0.0.1', $port = 6379, $db = 0)
    {
        $this->host = $host;
        $this->port = $port;
        $this->db = $db;
    }

    private function connect()
    {
        if (!$this->redis) {
            $this->redis = new Client([
                'scheme' => 'tcp',
                'host'   => $this->host,
                'port'   => $this->port,
            ]);

            $this->redis->select($this->db);
        }

        return $this->redis;
    }

    // Task
    public function addTask($email, $templateCode, $queueId)
    {
        $task = json_encode([
            'email' => $email,
            'templateCode' => $templateCode,
        ]);

        if ($this->taskExists($task, $queueId)) {
            return true;
        }

        return (bool)$this->connect()->sadd(static::REDIS_KEY . $queueId, $task);
    }

    public function getTask($queueId)
    {
        return $this->connect()->spop(static::REDIS_KEY . $queueId);
    }

    public function getTasksCount($queueId)
    {
        return $this->connect()->scard(static::REDIS_KEY . $queueId);
    }

    public function hasTasks($queueId)
    {
        return $this->getTasksCount($queueId) > 0;
    }

    public function taskExists($task, $queueId)
    {
        return (bool)$this->connect()->sismember(static::REDIS_KEY . $queueId, $task);
    }

    // Queue
    public function removeQueue($queueId)
    {
        return $this->connect()->del([static::REDIS_KEY . $queueId]) && $this->restartQueue($queueId);
    }

    public function pauseQueue($queueId)
    {
        return $this->connect()->sadd(static::REDIS_PAUSED_QUEUES_KEY, $queueId);
    }

    public function restartQueue($queueId)
    {
        return $this->connect()->srem(static::REDIS_PAUSED_QUEUES_KEY, $queueId);
    }

    public function isQueueActive($queueId)
    {
        return !((bool)$this->connect()->sismember(static::REDIS_PAUSED_QUEUES_KEY, $queueId));
    }
}
