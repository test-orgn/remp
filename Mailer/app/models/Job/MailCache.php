<?php

namespace Remp\MailerModule\Job;

interface MailCache
{
    /**
     * Adds mail job to mail processing cache
     *
     * Note: all parameters in $params having name with suffix '_href_url' are treated as URLs that can be changed later by email sender.
     * The URL destination itself will be kept, however, e.g. tracking parameters could be added, URL shortener used.
     * Example: https://dennikn.sk/1589603/ could be changed to https://dennikn.sk/1589603/?utm_source=email
     *
     * @param       $userId
     * @param       $email
     * @param       $templateCode
     * @param       $queueId
     * @param       $context
     * @param array $params contains array of key-value items that will replace variables in email and subject
     *
     * @return bool
     */
    public function addJob($userId, $email, $templateCode, $queueId, $context, $params = []): bool;

    public function getJob($queueId);

    public function getJobs($queueId, $count = 1): array;

    public function hasJobs($queueId);

    public function jobExists($job, $queueId);

    public function removeQueue($queueId);

    public function pauseQueue($queueId);

    public function restartQueue($queueId, $priority);

    public function isQueueActive($queueId);

    public function isQueueTopPriority($queueId);
}
