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

namespace MonsieurBiz\SyliusSettingsPlugin\Settings;

use MonsieurBiz\SyliusSettingsPlugin\Entity\Setting\SettingInterface;
use MonsieurBiz\SyliusSettingsPlugin\Exception\SettingsException;
use MonsieurBiz\SyliusSettingsPlugin\Form\AbstractSettingsType;
use MonsieurBiz\SyliusSettingsPlugin\Repository\SettingRepositoryInterface;
use Sylius\Component\Channel\Model\ChannelInterface;

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
     * @var array|null
     */
    private ?array $settingsByChannelAndLocaleWithDefault;

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
     * @throws SettingsException
     *
     * @return string
     */
    public function getFormClass(): string
    {
        $className = $this->metadata->getClass('form');
        if (!\in_array(AbstractSettingsType::class, class_parents($className), true)) {
            throw new SettingsException(sprintf('Class %s should extend %s', $className, AbstractSettingsType::class));
        }

        return $className;
    }

    /**
     * @param string $channelIdentifier
     * @param string $localeIdentifier
     * @param bool $withDefault
     *
     * @return array|null
     */
    private function getCachedSettingsByChannelAndLocale(string $channelIdentifier, string $localeIdentifier, bool $withDefault): ?array
    {
        // With default?
        $varName = $withDefault ? 'settingsByChannelAndLocaleWithDefault' : 'settingsByChannelAndLocale';
        if (!isset($this->{$varName}[$channelIdentifier])) {
            $this->{$varName}[$channelIdentifier] = [];

            return null;
        }
        if (!isset($this->{$varName}[$channelIdentifier][$localeIdentifier])) {
            return null;
        }

        return $this->{$varName}[$channelIdentifier][$localeIdentifier];
    }

    /**
     * @param ChannelInterface|null $channel
     * @param string|null $localeCode
     * @param bool $withDefault
     *
     * @return array
     */
    public function getSettingsByChannelAndLocale(?ChannelInterface $channel = null, ?string $localeCode = null, bool $withDefault = false): array
    {
        $channelIdentifier = null === $channel ? '___' . self::DEFAULT_KEY : (string) $channel->getCode();
        $localeIdentifier = null === $localeCode ? '___' . self::DEFAULT_KEY : $localeCode;

        if (null === $settings = $this->getCachedSettingsByChannelAndLocale($channelIdentifier, $localeIdentifier, $withDefault)) {
            if ($withDefault) {
                $settings = $this->stackSettings(
                    $this->settingRepository->findAllByChannelAndLocaleWithDefault(
                        $this->metadata->getApplicationName(),
                        $this->metadata->getName(true),
                        $channel,
                        $localeCode
                    )
                );
                $this->settingsByChannelAndLocaleWithDefault[$channelIdentifier][$localeIdentifier] = $settings;
            } else {
                $settings = $this->stackSettings(
                    $this->settingRepository->findAllByChannelAndLocale(
                        $this->metadata->getApplicationName(),
                        $this->metadata->getName(true),
                        $channel,
                        $localeCode
                    )
                );
                $this->settingsByChannelAndLocale[$channelIdentifier][$localeIdentifier] = $settings;
            }
        }

        return $settings;
    }

    /**
     * @param array $allSettings
     *
     * @return array
     */
    private function stackSettings(array $allSettings): array
    {
        $settings = [];
        /** @var SettingInterface $setting */
        // If we have the default values as well, the order is primordial.
        // We will store the default first, so the no default values will override the default if needed.
        foreach ($allSettings as $setting) {
            if (\is_array($setting)) {
                $setting = current($setting);
            }
            $settings[$setting->getPath()] = $setting;
        }

        return $settings;
    }

    /**
     * @param ChannelInterface|null $channel
     * @param string|null $localeCode
     *
     * @return array
     */
    public function getSettingsValuesByChannelAndLocale(?ChannelInterface $channel = null, ?string $localeCode = null): array
    {
        $allSettings = $this->getSettingsByChannelAndLocale($channel, $localeCode);
        $settingsValues = [];
        /** @var SettingInterface $setting */
        foreach ($allSettings as $setting) {
            $settingsValues[$setting->getPath()] = $setting->getValue();
        }

        return $settingsValues;
    }

    /**
     * @param ChannelInterface|null $channel
     * @param string|null $localeCode
     * @param string $path
     *
     * @return mixed
     */
    public function getCurrentValue(?ChannelInterface $channel, ?string $localeCode, string $path)
    {
        $settings = $this->getSettingsByChannelAndLocale($channel, $localeCode, true);
        if (isset($settings[$path])) {
            return $settings[$path]->getValue();
        }

        return null;
    }

    /**
     * {@inheritdoc}
     */
    public function showLocalesInForm(): bool
    {
        return $this->metadata->useLocales();
    }
}
