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

namespace MonsieurBiz\SyliusSettingsPlugin\Settings;

use MonsieurBiz\SyliusSettingsPlugin\Entity\Setting\SettingInterface;
use MonsieurBiz\SyliusSettingsPlugin\Exception\SettingsException;
use MonsieurBiz\SyliusSettingsPlugin\Form\AbstractSettingsType;
use MonsieurBiz\SyliusSettingsPlugin\Repository\SettingRepositoryInterface;
use Sylius\Component\Channel\Model\ChannelInterface;

final class Settings implements SettingsInterface
{
    public const DEFAULT_KEY = 'default';

    private Metadata $metadata;

    private SettingRepositoryInterface $settingRepository;

    private ?array $settingsByChannelAndLocale;

    private ?array $settingsByChannelAndLocaleWithDefault;

    /**
     * Settings constructor.
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
     */
    public function getFormClass(): string
    {
        $className = $this->metadata->getClass('form');
        $parentClassNames = (array) class_parents($className) ?? [];
        if (!\in_array(AbstractSettingsType::class, $parentClassNames, true)) {
            throw new SettingsException(sprintf('Class %s should extend %s', $className, AbstractSettingsType::class));
        }

        return $className;
    }

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
     * @SuppressWarnings(PHPMD.BooleanArgumentFlag)
     */
    public function getSettingsByChannelAndLocale(?ChannelInterface $channel = null, ?string $localeCode = null, bool $withDefault = false): array
    {
        $channelIdentifier = null === $channel ? '___' . self::DEFAULT_KEY : (string) $channel->getCode();
        $localeIdentifier = null === $localeCode ? '___' . self::DEFAULT_KEY : $localeCode;

        if (null === $settings = $this->getCachedSettingsByChannelAndLocale($channelIdentifier, $localeIdentifier, $withDefault)) {
            $settings = $this->getUncachedSettingsByChannelAndLocale($channel, $localeCode, $withDefault);
            $this->addSettingsByChannelAndLocale($settings, $channelIdentifier, $localeIdentifier, $withDefault);
        }

        return $settings;
    }

    /**
     * @SuppressWarnings(PHPMD.BooleanArgumentFlag)
     */
    private function getUncachedSettingsByChannelAndLocale(?ChannelInterface $channel = null, ?string $localeCode = null, bool $withDefault = false): array
    {
        $findArguments = [
            $this->metadata->getApplicationName(),
            $this->metadata->getName(true),
            $channel,
            $localeCode,
        ];

        if ($withDefault) {
            return $this->stackSettings(
                $this->settingRepository->findAllByChannelAndLocaleWithDefault(
                    ...$findArguments
                )
            );
        }

        return $this->stackSettings(
            $this->settingRepository->findAllByChannelAndLocale(
                ...$findArguments
            )
        );
    }

    /**
     * @SuppressWarnings(PHPMD.BooleanArgumentFlag)
     */
    private function addSettingsByChannelAndLocale(array $settings, string $channelIdentifier, string $localeIdentifier, bool $withDefault = false): void
    {
        if ($withDefault) {
            $this->settingsByChannelAndLocaleWithDefault[$channelIdentifier][$localeIdentifier] = $settings;

            return;
        }
        $this->settingsByChannelAndLocale[$channelIdentifier][$localeIdentifier] = $settings;
    }

    private function stackSettings(array $allSettings): array
    {
        $settings = [];
        /** @var SettingInterface|array $setting */
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

    public function getCurrentValue(?ChannelInterface $channel, ?string $localeCode, string $path)
    {
        $settings = $this->getSettingsByChannelAndLocale($channel, $localeCode, true);
        if (isset($settings[$path])) {
            return $settings[$path]->getValue();
        }

        return $this->getDefaultValue($path);
    }

    public function getDefaultValues(): array
    {
        return $this->metadata->getDefaultValues();
    }

    public function getDefaultValue(string $path)
    {
        $defaultValues = $this->getDefaultValues();
        if (\array_key_exists($path, $defaultValues)) {
            return $defaultValues[$path];
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
