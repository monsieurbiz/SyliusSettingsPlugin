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

use Sylius\Component\Channel\Model\ChannelInterface;

interface SettingsProviderInterface
{
    public function getSettingValue(string $alias, string $path): mixed;

    public function getSettingValueByChannelAndLocale(
        string $alias,
        string $path,
        ChannelInterface $channel,
        ?string $locale = null
    ): mixed;
}
