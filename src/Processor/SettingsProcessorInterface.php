<?php

declare(strict_types=1);

namespace MonsieurBiz\SyliusSettingsPlugin\Processor;

use MonsieurBiz\SyliusSettingsPlugin\Settings\SettingsInterface;

interface SettingsProcessorInterface
{
    public function processData(SettingsInterface $settings, array $data): void;
}
