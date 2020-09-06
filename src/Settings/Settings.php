<?php

declare(strict_types=1);

namespace MonsieurBiz\SyliusSettingsPlugin\Settings;

use MonsieurBiz\SyliusSettingsPlugin\Entity\Setting\Setting;
use MonsieurBiz\SyliusSettingsPlugin\Entity\Setting\SettingInterface;
use MonsieurBiz\SyliusSettingsPlugin\Exception\SettingsException;
use MonsieurBiz\SyliusSettingsPlugin\Form\AbstractSettingsType;
use MonsieurBiz\SyliusSettingsPlugin\Repository\SettingRepositoryInterface;
use Sylius\Component\Channel\Model\ChannelInterface;
use Sylius\Component\Locale\Model\LocaleInterface;

final class Settings implements SettingsInterface
{
    public const DEFAULT_KEY = 'default';

    /**
     * @var Metadata
     */
    private Metadata $metadata;

    /**
     * @var SettingRepositoryInterface
     */
    private SettingRepositoryInterface $settingRepository;

    /**
     * @var array|null
     */
    private ?array $settingsByChannelAndLocale;

    /**
     * Settings constructor.
     *
     * @param Metadata $metadata
     * @param SettingRepositoryInterface $settingRepository
     */
    public function __construct(Metadata $metadata, SettingRepositoryInterface $settingRepository)
    {
        $this->metadata = $metadata;
        $this->settingRepository = $settingRepository;
    }

    public function getAlias(): string
    {
        return $this->metadata->getAlias();
    }

    public function getAliasAsArray(): array
    {
        return [
            'vendor' => $this->metadata->getApplicationName(true),
            'plugin' => $this->metadata->getName(true),
        ];
    }

    public function getVendorName(): string
    {
        return $this->metadata->getParameter('vendor_name');
    }

    public function getVendorUrl(): ?string
    {
        return $this->metadata->getParameter('vendor_url');
    }

    public function getPluginName(): string
    {
        return $this->metadata->getParameter('plugin_name');
    }

    public function getDescription(): string
    {
        return $this->metadata->getParameter('description');
    }

    public function getIcon(): string
    {
        return $this->metadata->getParameter('icon');
    }

    /**
     * @return string
     * @throws SettingsException
     */
    public function getFormClass(): string
    {
        $className = $this->metadata->getClass('form');
        if (!in_array(AbstractSettingsType::class, class_parents($className))) {
            throw new SettingsException(sprintf('Class %s should extend %s', $className, AbstractSettingsType::class));
        }
        return $className;
    }

    private function getCachedSettingsByChannelAndLocale(string $channelIdentifier, string $localeIdentifier): ?array
    {
        if (!isset($this->settingsByChannelAndLocale[$channelIdentifier])) {
            $this->settingsByChannelAndLocale[$channelIdentifier] = [];
            return null;
        } elseif (!isset($this->settingsByChannelAndLocale[$channelIdentifier][$localeIdentifier])) {
            return null;
        }
        return $this->settingsByChannelAndLocale[$channelIdentifier][$localeIdentifier];
    }

    /**
     * @param ChannelInterface|null $channel
     * @param string|null $localeCode
     *
     * @param bool $withDefault
     *
     * @return array
     */
    public function getSettingsByChannelAndLocale(?ChannelInterface $channel = null, ?string $localeCode = null, bool $withDefault = false): array
    {
        $channelIdentifier = null === $channel ? '___' . self::DEFAULT_KEY : $channel->getCode();
        $localeIdentifier = null === $localeCode ? '___' . self::DEFAULT_KEY : $localeCode;
        if (null === $settings = $this->getCachedSettingsByChannelAndLocale($channelIdentifier, $localeIdentifier)) {
            $findAllByChannelAndLocale = $withDefault ? 'findAllByChannelAndLocaleWithDefault' : 'findAllByChannelAndLocale';
            $allSettings = $this->settingRepository->$findAllByChannelAndLocale(
                $this->metadata->getApplicationName(),
                $this->metadata->getName(true),
                $channel,
                $localeCode
            );
            $settings = [];
            /** @var SettingInterface $setting */
            // If we have the default values as well, the order is primordial.
            // We will store the default first, so the no default values will override the default if needed.
            foreach ($allSettings as $setting) {
                $settings[$setting->getPath()] = $setting;
            }
            $this->settingsByChannelAndLocale[$channelIdentifier][$localeIdentifier] = $settings;
        }
        return $settings;
    }

    /**
     * @param ChannelInterface|null $channel
     * @param LocaleInterface $locale
     *
     * @return array
     */
    public function getSettingsValuesByChannelAndLocale(?ChannelInterface $channel = null, ?LocaleInterface $locale = null): array
    {
        $allSettings = $this->getSettingsByChannelAndLocale($channel, null === $locale ? null : $locale->getCode());
        $settingsValues = [];
        /** @var SettingInterface $setting */
        foreach ($allSettings as $setting) {
            $settingsValues[$setting->getPath()] = $setting->getValue();
        }
        return $settingsValues;
    }
}
