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

namespace MonsieurBiz\SyliusSettingsPlugin\Processor;

use Doctrine\ORM\EntityManagerInterface;
use LogicException;
use MonsieurBiz\SyliusSettingsPlugin\Factory\SettingFactoryInterface;
use MonsieurBiz\SyliusSettingsPlugin\Settings\Settings;
use MonsieurBiz\SyliusSettingsPlugin\Settings\SettingsInterface;
use Sylius\Component\Channel\Repository\ChannelRepositoryInterface;
use Sylius\Component\Core\Model\ChannelInterface;
use Sylius\Component\Locale\Model\LocaleInterface;
use Sylius\Component\Resource\Repository\RepositoryInterface;
use TypeError;

final class SettingsProcessor implements SettingsProcessorInterface
{
    private ChannelRepositoryInterface $channelRepository;

    private RepositoryInterface $localeRepository;

    private EntityManagerInterface $entityManager;

    private SettingFactoryInterface $settingFactory;

    /**
     * SettingsProcessor constructor.
     */
    public function __construct(
        ChannelRepositoryInterface $channelRepository,
        RepositoryInterface $localeRepository,
        EntityManagerInterface $entityManager,
        SettingFactoryInterface $settingFactory
    ) {
        $this->channelRepository = $channelRepository;
        $this->localeRepository = $localeRepository;
        $this->entityManager = $entityManager;
        $this->settingFactory = $settingFactory;
    }

    public function processData(SettingsInterface $settings, array $data): void
    {
        foreach ($data as $settingsIdentifier => $settingsData) {
            if (!\is_array($settingsData)) {
                continue;
            }
            [$channelId, $localeCode] = $this->getChannelIdAndLocaleCodeFromSettingKey($settingsIdentifier);
            $this->saveSettings($settings, $channelId, $localeCode, $settingsData);
        }
    }

    /**
     * @SuppressWarnings(PHPMD.CyclomaticComplexity)
     */
    private function getChannelIdAndLocaleCodeFromSettingKey(string $settingKey): array
    {
        switch (true) {
            // Default website + Default locale
            case sprintf('%1$s-%1$s', Settings::DEFAULT_KEY) === $settingKey:
                return [null, null];
                // Default website + locale
            case 1 === preg_match(sprintf('`^%1$s-(?!%1$s)(?P<localeCode>.+)$`', Settings::DEFAULT_KEY), $settingKey, $matches):
                return [null, $matches['localeCode']];
                // Website + default locale
            case 1 === preg_match(sprintf('`^channel-(?P<channelId>[0-9]+)-%1$s$`', Settings::DEFAULT_KEY), $settingKey, $matches):
                return [(int) $matches['channelId'], null];
                // Website + locale
            case 1 === preg_match(sprintf('`^channel-(?P<channelId>[0-9]+)-(?!%1$s)(?P<localeCode>.+)$`', Settings::DEFAULT_KEY), $settingKey, $matches):
                return [(int) $matches['channelId'], $matches['localeCode']];
            default:
                throw new LogicException("Format of the setting's key is incorrect.");
        }
    }

    private function saveSettings(SettingsInterface $settings, ?int $channelId, ?string $localeCode, array $data): void
    {
        /** @var ChannelInterface|null $channel */
        $channel = null !== $channelId ? $this->channelRepository->find($channelId) : null;

        /** @var LocaleInterface|null $locale */
        $locale = null !== $localeCode ? $this->localeRepository->findOneBy(['code' => $localeCode]) : null;

        $actualSettings = $settings->getSettingsByChannelAndLocale(
            $channel,
            $localeCode
        );

        $this->removeUnusedSettings($data, $actualSettings);
        $this->saveNewAndExistingSettings($data, $actualSettings, $settings, $channel, $locale);

        $this->entityManager->flush();
    }

    /**
     * @SuppressWarnings(PHPMD.CyclomaticComplexity)
     */
    private function removeUnusedSettings(array &$data, array $settings): void
    {
        // Manage defaults, and remove actual settings with "use default value" checked
        foreach ($data as $key => $value) {
            // Is the setting a "use default value"?
            if (1 === preg_match(sprintf('`^(?P<key>.*)(?:___%1$s)$`', Settings::DEFAULT_KEY), $key, $matches)) {
                if (true === $value) {
                    if (isset($settings[$matches['key']])) {
                        $this->entityManager->remove($settings[$matches['key']]);
                    }
                    unset($data[$matches['key']]);
                }
                unset($data[$key]);
            }
        }
    }

    private function saveNewAndExistingSettings(array $data, array $actualSettings, SettingsInterface $settings, ?ChannelInterface $channel, ?LocaleInterface $locale): void
    {
        foreach ($data as $key => $value) {
            if (isset($actualSettings[$key])) {
                $setting = $actualSettings[$key];

                try {
                    $setting->setValue($value);
                    $this->entityManager->persist($setting);

                    continue;
                } catch (TypeError $e) {
                    // The type doesn't match, it could be normal, let's find the type out of the value.
                }
            }

            $setting = $this->settingFactory->createNewFromGlobalSettings($settings, $channel, $locale);
            $setting->setPath($key);
            $setting->setStorageTypeFromValue($value);
            $setting->setValue($value);
            $this->entityManager->persist($setting);
        }
    }
}
