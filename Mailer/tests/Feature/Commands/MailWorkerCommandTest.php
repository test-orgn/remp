<?php
namespace Tests\Feature\Comands;

use Psr\Log\NullLogger;
use Remp\MailerModule\ActiveRow;
use Remp\MailerModule\Beam\UnreadArticlesResolver;
use Remp\MailerModule\Commands\ProcessJobCommand;
use Remp\MailerModule\Job\BatchEmailGenerator;
use Remp\MailerModule\Job\MailCache;
use Remp\MailerModule\Repository\BatchesRepository;
use Remp\MailerModule\Repository\JobQueueRepository;
use Remp\MailerModule\Repository\JobsRepository;
use Remp\MailerModule\Segment\Aggregator;
use Remp\MailerModule\User\IUser;
use Symfony\Component\Console\Tester\CommandTester;
use Tests\Feature\DatabaseTestCase;

class MailWorkerCommandTest extends DatabaseTestCase
{
    /** @var ProcessJobCommand */
    private $mailProcessJobCommand;

    protected function setUp()
    {
        parent::setUp();

        $this->mailProcessJobCommand = $this->inject(ProcessJobCommand::class);
    }


    public function testAddingUtmParamsToHrefUrls()
    {
        // TODO:
        // Create email job + batch + mail template
        $mailType = $this->createMailTypeWithCategory();
        $layout = $this->createMailLayout();
        $template = $this->createTemplate($layout, $mailType);
        $batch = $this->createBatch($template);

        // Run process job command
        $tester = new CommandTester($this->mailProcessJobCommand);
        $tester->execute([]);

        // Run command
        // Assert what email is sent
    }
}