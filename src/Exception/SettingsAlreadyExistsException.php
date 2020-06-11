<?php

declare(strict_types=1);

namespace MonsieurBiz\SyliusSettingsPlugin\Exception;

use Exception;
use MonsieurBiz\SyliusSettingsPlugin\Settings\SettingsInterface;

class SettingsAlreadyExistsException extends Exception
{
    public function __construct(SettingsInterface $settings)
    {
        parent::__construct(
            sprintf("Settings instance aliased '%s' already exists.", $settings->getAlias())
        );
    }
}
