<?php

/*
 * This file is part of Monsieur Biz' Settings plugin for Sylius.
 *
 * (c) Monsieur Biz <sylius@monsieurbiz.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

declare(strict_types=1);

namespace MonsieurBiz\SyliusSettingsPlugin\Settings;

use MonsieurBiz\SyliusSettingsPlugin\Exception\SettingsAlreadyExistsException;

final class Registry implements RegistryInterface
{
    private array $settings = [];

    /**
     * @throws SettingsAlreadyExistsException
     */
    public function addSettingsInstance(SettingsInterface $settings): void
    {
        if ($this->hasSettingsInstance($settings)) {
            throw new SettingsAlreadyExistsException($settings);
        }
        $this->settings[] = $settings;
    }

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

    public function getByAlias(string $alias): ?SettingsInterface
    {
        foreach ($this->settings as $settings) {
            if ($settings->getAlias() === $alias) {
                return $settings;
            }
        }

        return null;
    }

    public function count(): int
    {
        return \count($this->settings);
    }
}
