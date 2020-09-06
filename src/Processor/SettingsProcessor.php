<?php

declare(strict_types=1);

namespace MonsieurBiz\SyliusSettingsPlugin\Processor;

use Doctrine\ORM\EntityManagerInterface;
use MonsieurBiz\SyliusSettingsPlugin\Entity\Setting\SettingInterface;
use MonsieurBiz\SyliusSettingsPlugin\Factory\SettingFactoryInterface;
use MonsieurBiz\SyliusSettingsPlugin\Repository\SettingRepositoryInterface;
use MonsieurBiz\SyliusSettingsPlugin\Settings\Settings;
use MonsieurBiz\SyliusSettingsPlugin\Settings\SettingsInterface;
use Sylius\Component\Channel\Repository\ChannelRepositoryInterface;
use Sylius\Component\Core\Model\ChannelInterface;
use Sylius\Component\Locale\Converter\LocaleConverter;
use Sylius\Component\Locale\Model\LocaleInterface;
use Sylius\Component\Resource\Factory\FactoryInterface;
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

    public function processData(SettingsInterface $settings, array $data): void
    {
        foreach ($data as $settingsIdentifier => $settingsData) {
            if (!is_array($settingsData)) {
                continue;
            }
            switch (true) {
                // Default website + Default locale
                case sprintf('%1$s-%1$s', Settings::DEFAULT_KEY) === $settingsIdentifier:
                    $this->saveSettings($settings, null, null, $settingsData);
                    break;

                // Default website + locale
                case preg_match(sprintf('`^%1$s-(?!%1$s)(?P<localeCode>.+)$`', Settings::DEFAULT_KEY), $settingsIdentifier, $matches):
                    $this->saveSettings($settings, null, $matches['localeCode'], $settingsData);
                    break;

                // Website + default locale
                case preg_match(sprintf('`^channel-(?P<channelId>[0-9]+)-%1$s$`', Settings::DEFAULT_KEY), $settingsIdentifier, $matches):
                    $this->saveSettings($settings, (int) $matches['channelId'], null, $settingsData);
                    break;

                // Website + locale
                case preg_match(sprintf('`^channel-(?P<channelId>[0-9]+)-(?!%1$s)(?P<localeCode>.+)$`', Settings::DEFAULT_KEY), $settingsIdentifier, $matches):
                    $this->saveSettings($settings, (int) $matches['channelId'], $matches['localeCode'], $settingsData);
                    break;
            }
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
            $locale
        );

        // Manage defaults, and remove actual settings with "use default value" checked
        foreach ($data as $key => $value) {
            // Is a "use default value"?
            if (preg_match(sprintf('`^(?P<key>.*)(?:___%1$s)$`', Settings::DEFAULT_KEY), $key, $matches)) {
                if ($data[$key]) {
                    if (isset($actualSettings[$matches['key']])) {
                        $this->em->remove($actualSettings[$matches['key']]);
                    }
                    if (isset($data[$matches['key']])) {
                        unset($data[$matches['key']]);
                    }
                }
                unset($data[$key]);
            }
        }

        // Save the others
        foreach ($data as $key => $value) {
            if (isset($actualSettings[$key])) {
                $setting = $actualSettings[$key];
                $setting->setValue($value);
            } else {
                if (!is_null($value)) {
                    $setting = $this->settingFactory->createNewFromGlobalSettings($settings, $channel, $locale);
                    $setting->setPath($key);
                    $setting->setStorageTypeFromValue($value);
                    $setting->setValue($value);
                }
            }
            if (isset($setting)) {
                $this->em->persist($setting);
            }
        }

        $this->em->flush();
    }
}
