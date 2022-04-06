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

namespace MonsieurBiz\SyliusSettingsPlugin\Factory;

use MonsieurBiz\SyliusSettingsPlugin\Entity\Setting\SettingInterface;
use MonsieurBiz\SyliusSettingsPlugin\Settings\SettingsInterface;
use Sylius\Component\Core\Model\ChannelInterface;
use Sylius\Component\Locale\Model\LocaleInterface;
use Sylius\Component\Resource\Factory\FactoryInterface;

interface SettingFactoryInterface extends FactoryInterface
{
    public function createNewFromGlobalSettings(SettingsInterface $settings, ?ChannelInterface $channel, ?LocaleInterface $locale): SettingInterface;
}
