<?php
declare(strict_types=1);

namespace Remp\MailerModule\Sender;

use Nette\Mail\IMailer;
use Remp\MailerModule\Config\Config;
use Remp\MailerModule\Mailer\Mailer;

class MailerFactory
{
    /** @var  Config */
    private $config;

    /** @var array */
    private $availableMailers;

    public function __construct(Config $config)
    {
        $this->config = $config;
    }

    /**
     * @param IMailer $mailer
     */
    public function addMailer(IMailer $mailer): void
    {
        $this->availableMailers[$mailer->getAlias()] = $mailer;
    }

    /**
     * @param null|string $alias - If $alias is null, default mailer is returned.
     * @return IMailer|Mailer
     * @throws MailerNotExistsException|\Remp\MailerModule\Config\ConfigNotExistsException
     */
    public function getMailer(?string $alias = null): IMailer
    {
        if ($alias === null) {
            $alias = $this->config->get('default_mailer');
        }

        if (!isset($this->availableMailers[$alias])) {
            throw new MailerNotExistsException("Mailer {$alias} not exists");
        }

        return $this->availableMailers[$alias];
    }

    /**
     * @return IMailer[]
     */
    public function getAvailableMailers(): array
    {
        return $this->availableMailers;
    }
}
