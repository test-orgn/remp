<?php

namespace Remp\MailerModule\Repository;

use Nette\Database\Table\IRow;
use Remp\MailerModule\Repository;

class BatchesRepository extends Repository
{
    const STATE_CREATED = 'created';
    const STATE_UPDATED = 'updated';
    const STATE_READY = 'ready';
    const STATE_PREPARING = 'preparing';
    const STATE_PROCESSING = 'processing';
    const STATE_PROCESSED = 'processed';
    const STATE_SENDING = 'sending';
    const STATE_DONE = 'done';
    const STATE_USER_STOP = 'user_stopped';
    const STATE_WORKER_STOP = 'worker_stopped';

    protected $tableName = 'mail_job_batch';

    protected $dataTableSearchable = [];

    public function all()
    {
        return $this->getTable();
    }

    public function add()
    {
        $result = $this->insert([]);

        if (is_numeric($result)) {
            return $this->getTable()->where('id', $result)->fetch();
        }

        return $result;
    }

    public function update(IRow &$row, $data)
    {
        $params['updated_at'] = new \DateTime();
        return parent::update($row, $data);
    }

    public function getActiveBatches($jobId)
    {
        $selection = $this->getTable()->where('status', [
            BatchesRepository::STATE_PROCESSING,
            BatchesRepository::STATE_PROCESSED,
            BatchesRepository::STATE_SENDING,
        ]);

        if ($jobId !== null) {
            $selection->where('mail_job_id', $jobId);
        }

        return $selection;
    }

    public function tableFilter($query, $order, $orderDirection)
    {
        $selection = $this->getTable()
            ->order($order . ' ' . strtoupper($orderDirection));

        if (!empty($query)) {
            $where = [];
            foreach ($this->dataTableSearchable as $col) {
                $where[$col . ' LIKE ?'] = '%' . $query . '%';
            }

            $selection->whereOr($where);
        }

        return $selection->fetchAll();
    }
}
