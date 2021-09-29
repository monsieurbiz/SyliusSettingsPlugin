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

use MonsieurBiz\SyliusSettingsPlugin\Exception\SettingsException;
use MonsieurBiz\SyliusSettingsPlugin\Repository\SettingRepositoryInterface;
use Sylius\Component\Channel\Model\ChannelInterface;

interface SettingsInterface
{
    public function __construct(Metadata $metadata, SettingRepositoryInterface $settingRepository);

    public function getAlias(): string;

    public function getAliasAsArray(): array;

    public function getVendorName(): string;

    public function getVendorUrl(): ?string;

    public function getPluginName(): string;

    public function getDescription(): string;

    public function getIcon(): string;

    /**
     * @throws SettingsException
     */
    public function getFormClass(): string;

    /**
     * @SuppressWarnings(PHPMD.BooleanArgumentFlag)
     */
    public function getSettingsByChannelAndLocale(?ChannelInterface $channel = null, ?string $localeCode = null, bool $withDefault = false): array;

    public function getSettingsValuesByChannelAndLocale(?ChannelInterface $channel = null, ?string $localeCode = null): array;

    /**
     * @return mixed
     */
    public function getCurrentValue(?ChannelInterface $channel, ?string $localeCode, string $path);

    public function getDefaultValues(): array;

    /**
     * @return mixed
     */
    public function getDefaultValue(string $path);

    public function showLocalesInForm(): bool;
}
