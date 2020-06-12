<?php

declare(strict_types=1);

namespace MonsieurBiz\SyliusSettingsPlugin\Settings;

use MonsieurBiz\SyliusSettingsPlugin\Exception\SettingsException;

interface SettingsInterface
{
    public function __construct(Metadata $metadata);
    public function getAlias(): string;
    public function getVendorName(): string;
    public function getVendorUrl(): ?string;
    public function getPluginName(): string;
    public function getDescription(): string;
    public function getIcon(): string;

    /**
     * @throws SettingsException
     */
    public function getFormClass(): string;
}
