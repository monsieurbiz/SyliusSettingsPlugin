<?php

declare(strict_types=1);

namespace MonsieurBiz\SyliusSettingsPlugin\Factory\Form;

use MonsieurBiz\SyliusSettingsPlugin\Settings\SettingsInterface;
use Symfony\Component\Form\FormInterface;

interface MainSettingsFormTypeFactoryInterface
{
    public function createNew(SettingsInterface $settings, string $type, array $options = []): FormInterface;
}
