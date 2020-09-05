<?php

declare(strict_types=1);

namespace MonsieurBiz\SyliusSettingsPlugin\Settings;

use MonsieurBiz\SyliusSettingsPlugin\Exception\SettingsException;
use MonsieurBiz\SyliusSettingsPlugin\Repository\SettingRepositoryInterface;
use Sylius\Component\Core\Model\ChannelInterface;
use Sylius\Component\Locale\Model\LocaleInterface;

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

    public function getSettingsByChannelAndLocale(?ChannelInterface $channel = null, ?LocaleInterface $locale = null): array;
    public function getSettingsValuesByChannelAndLocale(?ChannelInterface $channel = null, ?LocaleInterface $locale = null): array;
}
