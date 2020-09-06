<?php

declare(strict_types=1);

namespace MonsieurBiz\SyliusSettingsPlugin\Repository;

use Sylius\Component\Channel\Model\ChannelInterface;
use Sylius\Component\Resource\Repository\RepositoryInterface;

interface SettingRepositoryInterface extends RepositoryInterface
{
    public function findAllByChannelAndLocale(string $vendor, string $plugin, ChannelInterface $channel = null, ?string $localeCode = null): array;
    public function findAllByChannelAndLocaleWithDefault(string $vendor, string $plugin, ChannelInterface $channel = null, ?string $localeCode = null): array;
}
