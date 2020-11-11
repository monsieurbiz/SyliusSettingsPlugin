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

namespace MonsieurBiz\SyliusSettingsPlugin\Processor;

use Doctrine\ORM\EntityManagerInterface;
use LogicException;
use MonsieurBiz\SyliusSettingsPlugin\Factory\SettingFactoryInterface;
use MonsieurBiz\SyliusSettingsPlugin\Repository\SettingRepositoryInterface;
use MonsieurBiz\SyliusSettingsPlugin\Settings\Settings;
use MonsieurBiz\SyliusSettingsPlugin\Settings\SettingsInterface;
use Sylius\Component\Channel\Repository\ChannelRepositoryInterface;
use Sylius\Component\Core\Model\ChannelInterface;
use Sylius\Component\Locale\Model\LocaleInterface;
use Sylius\Component\Resource\Repository\RepositoryInterface;

final class SettingsProcessor implements SettingsProcessorInterface
{
    /**
     * @var ChannelRepositoryInterface
     */
    private ChannelRepositoryInterface $channelRepository;

    /**
     * @var RepositoryInterface
     */
    private RepositoryInterface $localeRepository;

    /**
     * @var SettingRepositoryInterface
     */
    private SettingRepositoryInterface $settingRepository;

    /**
     * @var EntityManagerInterface
     */
    private EntityManagerInterface $em;

    /**
     * @var SettingFactoryInterface
     */
    private SettingFactoryInterface $settingFactory;

    /**
     * SettingsProcessor constructor.
     *
     * @param ChannelRepositoryInterface $channelRepository
     * @param RepositoryInterface $localeRepository
     * @param SettingRepositoryInterface $settingRepository
     * @param EntityManagerInterface $em
     * @param SettingFactoryInterface $settingFactory
     */
    public function __construct(
        ChannelRepositoryInterface $channelRepository,
        RepositoryInterface $localeRepository,
        SettingRepositoryInterface $settingRepository,
        EntityManagerInterface $em,
        SettingFactoryInterface $settingFactory
    ) {
        $this->channelRepository = $channelRepository;
        $this->localeRepository = $localeRepository;
        $this->settingRepository = $settingRepository;
        $this->em = $em;
        $this->settingFactory = $settingFactory;
    }

    /**
     * @param SettingsInterface $settings
     * @param array $data
     */
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
     * @param $settingKey
     *
     * @return array
     */
    private function getChannelIdAndLocaleCodeFromSettingKey($settingKey): array
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

    /**
     * @param SettingsInterface $settings
     * @param int|null $channelId
     * @param string|null $localeCode
     * @param array $data
     */
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

        $this->em->flush();
    }

    /**
     * @param array $data
     * @param array $settings
     */
    private function removeUnusedSettings(array &$data, array $settings): void
    {
        // Manage defaults, and remove actual settings with "use default value" checked
        foreach ($data as $key => $value) {
            // Is the setting a "use default value"?
            if (1 === preg_match(sprintf('`^(?P<key>.*)(?:___%1$s)$`', Settings::DEFAULT_KEY), $key, $matches)) {
                if (true === $data[$key]) {
                    if (isset($settings[$matches['key']])) {
                        $this->em->remove($settings[$matches['key']]);
                    }
                    unset($data[$matches['key']]);
                }
                unset($data[$key]);
            }
        }
    }

    /**
     * @param array $data
     * @param array $actualSettings
     * @param SettingsInterface $settings
     * @param ChannelInterface|null $channel
     * @param LocaleInterface|null $locale
     */
    private function saveNewAndExistingSettings(array $data, array $actualSettings, SettingsInterface $settings, ?ChannelInterface $channel, ?LocaleInterface $locale): void
    {
        foreach ($data as $key => $value) {
            if (isset($actualSettings[$key])) {
                $setting = $actualSettings[$key];
                try {
                    $setting->setValue($value);
                    $this->em->persist($setting);
                    continue;
                } catch (\TypeError $e) {
                    // The type doesn't match, it could be normal, let's find the type out of the value.
                }
            }

            $setting = $this->settingFactory->createNewFromGlobalSettings($settings, $channel, $locale);
            $setting->setPath($key);
            $setting->setStorageTypeFromValue($value);
            $setting->setValue($value);
            $this->em->persist($setting);
        }
    }
}
