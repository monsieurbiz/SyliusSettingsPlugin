<?php

/*
 * This file is part of Monsieur Biz' Settings plugin for Sylius.
 *
 * (c) Monsieur Biz <sylius@monsieurbiz.com>
 *
 * For the full copyright and license information, please view the LICENSE.txt
 * file that was distributed with this source code.
 */

declare(strict_types=1);

namespace MonsieurBiz\SyliusSettingsPlugin\Settings;

use Countable;
use MonsieurBiz\SyliusSettingsPlugin\Exception\SettingsAlreadyExistsException;

interface RegistryInterface extends Countable
{
    public function count(): int;

    /**
     * @throws SettingsAlreadyExistsException
     */
    public function addSettingsInstance(SettingsInterface $settings): void;

    public function hasSettingsInstance(SettingsInterface $settings): bool;

    /**
     * @return array<SettingsInterface>
     */
    public function getAllSettings(): array;

    public function getByAlias(string $alias): ?SettingsInterface;
}
