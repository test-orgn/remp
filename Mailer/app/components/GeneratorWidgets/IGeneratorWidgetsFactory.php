<?php

namespace Remp\MailerModule\Components;

interface IGeneratorWidgetsFactory
{
    /**
     * @param int $sourceTemplateId
     *
     * @return GeneratorWidgets
     */
    public function create(int $sourceTemplateId): GeneratorWidgets;
}
