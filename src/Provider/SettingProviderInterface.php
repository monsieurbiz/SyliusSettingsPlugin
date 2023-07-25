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

namespace MonsieurBiz\SyliusSettingsPlugin\Provider;

use MonsieurBiz\SyliusSettingsPlugin\Entity\Setting\SettingInterface;
use Sylius\Component\Core\Model\ChannelInterface;

interface SettingProviderInterface
{
    public function getSettingOrCreateNew(string $vendor, string $plugin, ?string $path, ?string $locale, ?ChannelInterface $channel): SettingInterface;

    public function validateType(?string $type): void;

    public function resetExistingValue(SettingInterface $setting): SettingInterface;
}
