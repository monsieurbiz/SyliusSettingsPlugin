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

namespace MonsieurBiz\SyliusSettingsPlugin\Attribute;

use Attribute;
use MonsieurBiz\SyliusSettingsPlugin\Settings\Settings;

#[Attribute(Attribute::TARGET_CLASS)]
final class AsSetting
{
    public function __construct(
        public string $alias,
        public string $vendorName,
        public ?string $pluginName,
        public ?string $description,
        public string $icon,
        public bool $useLocales,
        public array $defaultValues = [],
        public string $vendorUrl = '',
        public string $settingsClass = Settings::class,
    ) {
    }

    public function getConfiguration(): array
    {
        return [
            'alias' => $this->alias,
            'vendor_name' => $this->vendorName,
            'vendor_url' => $this->vendorUrl,
            'plugin_name' => $this->pluginName,
            'description' => $this->description,
            'icon' => $this->icon,
            'use_locales' => $this->useLocales,
            'default_values' => $this->defaultValues,
            'classes' => [
                'settings' => $this->settingsClass,
            ],
        ];
    }
}
