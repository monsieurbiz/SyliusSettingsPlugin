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

namespace MonsieurBiz\SyliusSettingsPlugin\CacheWarmer;

use Doctrine\ORM\EntityManagerInterface;
use Exception;
use MonsieurBiz\SyliusSettingsPlugin\Settings\RegistryInterface;
use Sylius\Component\Channel\Repository\ChannelRepositoryInterface;
use Sylius\Component\Core\Model\ChannelInterface;
use Sylius\Component\Locale\Model\LocaleInterface;

final class SettingsCacheWarmer implements SettingsCacheWarmerInterface
{
    public function __construct(
        private ChannelRepositoryInterface $channelRepository,
        private RegistryInterface $registry,
        private EntityManagerInterface $entityManager,
    ) {
    }

    public function isOptional(): bool
    {
        return false;
    }

    /**
     * @SuppressWarnings(PHPMD.UnusedFormalParameters)
     * @SuppressWarnings(PHPMD.CyclomaticComplexity)
     */
    public function warmUp(string $cacheDir, ?string $buildDir = null): array
    {
        if (
            false === $this->entityManager->getConnection()->isConnected()
            || false === $this->entityManager->getConnection()->getSchemaManager()->tablesExist('mbiz_settings_setting')
        ) {
            return [];
        }

        try {
            $settings = $this->registry->getAllSettings();
            foreach ($settings as $setting) {
                $setting->getSettingsByChannelAndLocale();
                $setting->getSettingsByChannelAndLocale(null, null, true);

                /** @var ChannelInterface $channel */
                foreach ($this->channelRepository->findAll() as $channel) {
                    if (null === $channel->getCode()) {
                        continue;
                    }

                    $setting->getSettingsByChannelAndLocale($channel);
                    $setting->getSettingsByChannelAndLocale($channel, null, true);

                    /** @var LocaleInterface $locale */
                    foreach ($channel->getLocales() as $locale) {
                        $setting->getSettingsByChannelAndLocale($channel, $locale->getCode());
                        $setting->getSettingsByChannelAndLocale($channel, $locale->getCode(), true);
                    }
                }
            }
        } catch (Exception) {
            // If an exception is thrown, we just ignore it and return an empty array
            // e.g. if upgrade Sylius and a new channel property is added to the ChannelInterface
        }

        return [];
    }
}
