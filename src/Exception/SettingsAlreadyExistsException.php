<?php

declare(strict_types=1);

namespace MonsieurBiz\SyliusSettingsPlugin\Exception;

class SettingsAlreadyExistsException extends SettingsException
{
    public function __construct(SettingsInterface $settings)
    {
        parent::__construct(
            sprintf("Settings instance aliased '%s' already exists.", $settings->getAlias())
        );
    }
}
