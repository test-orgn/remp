<?php
declare(strict_types=1);

namespace Remp\MailerModule\Repositories;

use Nette\Utils\DateTime;
use Remp\MailerModule\Repositories;

class MailTemplateStatsRepository extends Repository
{
    protected $tableName = 'mail_template_stats';

    public function byDateAndMailTemplateId(DateTime $date, int $id)
    {
        return $this->getTable()
            ->where('mail_template_id', $id)
            ->where('date', $date->format('Y-m-d'))
            ->fetch();
    }

    public function byMailTemplateId(int $id)
    {
        return $this->getTable()
            ->where('mail_template_id', $id);
    }

    public function byMailTypeId(int $id)
    {
        return $this->getTable()
            ->where('mail_template.mail_type_id', $id)
            ->group('mail_template.mail_type_id');
    }

    /**
     * @return \Remp\MailerModule\Repositories\Selection
     */
    public function all()
    {
        return $this->getTable();
    }

    public function getMailTypeGraphData(int $mailTypeId, DateTime $from, DateTime $to)
    {
        return $this->getTable()
            ->select('
                SUM(COALESCE(mail_template_stats.sent, 0)) AS sent_mails,
                SUM(COALESCE(mail_template_stats.opened, 0)) AS opened_mails,
                SUM(COALESCE(mail_template_stats.clicked, 0)) AS clicked_mails,
                date AS label_date')
            ->where('mail_template.mail_type_id = ?', $mailTypeId)
            ->where('date >= DATE(?)', $from)
            ->where('date <= DATE(?)', $to)
            ->group('
                label_date
            ')
            ->order('label_date DESC');
    }

    public function getAllMailTemplatesGraphData(DateTime $from, DateTime $to)
    {
        return $this->getTable()
            ->select('
                SUM(COALESCE(mail_template_stats.sent, 0)) AS sent_mails,
                date
            ')
            ->where('date > DATE(?)', $from)
            ->where('date <= DATE(?)', $to)
            ->group('date');
    }

    public function getTemplatesGraphDataGroupedByMailType(DateTime $from, DateTime $to)
    {
        return $this->getTable()
            ->select('
                SUM(COALESCE(mail_template_stats.sent, 0)) AS sent_mails,
                mail_template.mail_type_id,
                date
            ')
            ->where('mail_template.mail_type_id IS NOT NULL')
            ->where('mail_template_stats.date >= DATE(?)', $from)
            ->where('mail_template_stats.date <= DATE(?)', $to)
            ->group('
                date,
                mail_template.mail_type_id
            ')
            ->order('mail_template.mail_type_id')
            ->order('mail_template_stats.date DESC');
    }
}