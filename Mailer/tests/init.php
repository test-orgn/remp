<?php

require __DIR__ . '/../app/Bootstrap.php';

$configurator = Remp\MailerModule\Repositories\Bootstrap::boot();
$container = $configurator->createContainer();
