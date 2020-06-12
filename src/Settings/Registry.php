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
        return !(null === $this->getByAlias($settings->getAlias()));
    }

    /**
     * @return array<SettingsInterface>
     */
    public function getAllSettings(): array
    {
        return $this->settings;
    }

    /**
     * @param string $alias
     *
     * @return SettingsInterface|null
     */
    public function getByAlias(string $alias): ?SettingsInterface
    {
        foreach ($this->settings as $settings) {
            if ($settings->getAlias() === $alias) {
                return $settings;
            }
        }
        return null;
    }

    /**
     * @return int
     */
    public function count(): int
    {
        return count($this->settings);
    }
}
