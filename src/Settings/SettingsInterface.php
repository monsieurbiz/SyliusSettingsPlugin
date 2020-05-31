<?php

declare(strict_types=1);

namespace MonsieurBiz\SyliusSettingsPlugin\Settings;

interface SettingsInterface
{
    public function getVendorName(): string;
    public function getVendorUrl(): ?string;
    public function getPluginName(): string;
//    public function getForm(): SettingsTypeInterface;
//    public function getConfig($path, ?ChannelInterface $channel = null, ?string $localeCode = null);
}
