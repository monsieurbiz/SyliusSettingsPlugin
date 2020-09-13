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

use Countable;
use MonsieurBiz\SyliusSettingsPlugin\Exception\SettingsAlreadyExistsException;

interface RegistryInterface extends Countable
{
    /**
     * @return int
     */
    public function count(): int;

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

    /**
     * @param string $alias
     *
     * @return SettingsInterface|null
     */
    public function getByAlias(string $alias): ?SettingsInterface;
}
