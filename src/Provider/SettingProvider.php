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

use Exception;
use MonsieurBiz\SyliusSettingsPlugin\Entity\Setting\Setting;
use MonsieurBiz\SyliusSettingsPlugin\Entity\Setting\SettingInterface;
use MonsieurBiz\SyliusSettingsPlugin\Repository\SettingRepositoryInterface;
use Sylius\Component\Core\Model\ChannelInterface;
use Sylius\Component\Resource\Factory\FactoryInterface;

class SettingProvider implements SettingProviderInterface
{
    private SettingRepositoryInterface $settingRepository;

    private FactoryInterface $settingFactory;

    public function __construct(
        SettingRepositoryInterface $settingRepository,
        FactoryInterface $settingFactory
    ) {
        $this->settingRepository = $settingRepository;
        $this->settingFactory = $settingFactory;
    }

    public function getSettingOrCreateNew(string $vendor, string $plugin, ?string $path, ?string $locale, ?ChannelInterface $channel): SettingInterface
    {
        /** @var SettingInterface|null $setting */
        $setting = $this->settingRepository->findOneBy([
            'vendor' => $vendor,
            'plugin' => $plugin,
            'path' => $path,
            'localeCode' => $locale,
            'channel' => $channel,
        ]);

        if (null === $setting) {
            /** @var SettingInterface $setting */
            $setting = $this->settingFactory->createNew();
            $setting->setVendor($vendor);
            $setting->setPlugin($plugin);
            $setting->setPath($path);
            $setting->setLocaleCode($locale);
            $setting->setChannel($channel);
        }

        return $setting;
    }

    public function validateType(string $type): void
    {
        $types = Setting::getAllStorageTypes();
        if (!\in_array($type, $types, true)) {
            throw new Exception(sprintf('The type "%s" is not valid. Valid types are: %s', $type, implode(', ', $types)));
        }
    }

    public function resetExistingValue(SettingInterface $setting): SettingInterface
    {
        if (null !== $setting->getStorageType()) {
            // Reset existing value
            $setting->setValue(null);
        }

        return $setting;
    }
}
