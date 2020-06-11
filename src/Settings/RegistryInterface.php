<?php

declare(strict_types=1);

namespace MonsieurBiz\SyliusSettingsPlugin\Settings;

use MonsieurBiz\SyliusSettingsPlugin\Exception\SettingsAlreadyExistsException;

interface RegistryInterface
{
    /**
     * @param SettingsInterface $settings
     *
     * @throws SettingsAlreadyExistsException
     */
    public function addSettingsInstance(SettingsInterface $settings): void;

    /**
     * @param SettingsInterface $settings
     *
     * @return bool
     */
    public function hasSettingsInstance(SettingsInterface $settings): bool;

    /**
     * @return array<SettingsInterface>
     */
    public function getAllSettings(): array;
}
