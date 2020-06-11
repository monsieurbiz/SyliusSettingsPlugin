<?php

declare(strict_types=1);

namespace MonsieurBiz\SyliusSettingsPlugin\Settings;

use MonsieurBiz\SyliusSettingsPlugin\Exception\SettingsAlreadyExistsException;

final class Registry implements RegistryInterface
{

    /**
     * @var array
     */
    private array $settings = [];

    /**
     * @param SettingsInterface $settings
     *
     * @throws SettingsAlreadyExistsException
     */
    public function addSettingsInstance(SettingsInterface $settings): void
    {
        if ($this->hasSettingsInstance($settings)) {
            throw new SettingsAlreadyExistsException($settings);
        }
        $this->settings[] = $settings;
    }

    /**
     * @param SettingsInterface $settings
     *
     * @return bool
     */
    public function hasSettingsInstance(SettingsInterface $settings): bool
    {
        foreach ($this->settings as $settingsToCompare) {
            if ($settings->getAlias() === $settingsToCompare->getAlias()) {
                return true;
            }
        }
        return false;
    }

    /**
     * @return array<SettingsInterface>
     */
    public function getAllSettings(): array
    {
        return $this->settings;
    }
}
