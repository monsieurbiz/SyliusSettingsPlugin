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
use Symfony\Contracts\Cache\ItemInterface;
use Symfony\Contracts\Cache\TagAwareCacheInterface;

final class Settings implements SettingsInterface
{
    public const DEFAULT_KEY = 'default';

    public function __construct(
        private Metadata $metadata,
        private SettingRepositoryInterface $settingRepository,
        private TagAwareCacheInterface $monsieurbizSettingsCache
    ) {
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

    public function getVendorName(): ?string
    {
        /** @phpstan-ignore-next-line */
        return (string) $this->metadata->getParameter('vendor_name');
    }

    public function getVendorUrl(): ?string
    {
        /** @phpstan-ignore-next-line */
        return (string) $this->metadata->getParameter('vendor_url');
    }

    public function getPluginName(): ?string
    {
        /** @phpstan-ignore-next-line */
        return (string) $this->metadata->getParameter('plugin_name');
    }

    public function getDescription(): ?string
    {
        /** @phpstan-ignore-next-line */
        return (string) $this->metadata->getParameter('description');
    }

    public function getIcon(): ?string
    {
        /** @phpstan-ignore-next-line */
        return (string) $this->metadata->getParameter('icon');
    }

    /**
     * @throws SettingsException
     */
    public function getFormClass(): string
    {
        $className = $this->metadata->getClass('form');
        $parentClassNames = (array) (class_parents($className) ?: []);
        if (!\in_array(AbstractSettingsType::class, $parentClassNames, true)) {
            throw new SettingsException(\sprintf('Class %s should extend %s', $className, AbstractSettingsType::class));
        }

        return $className;
    }

    /**
     * @SuppressWarnings(PHPMD.BooleanArgumentFlag)
     */
    public function getSettingsByChannelAndLocale(?ChannelInterface $channel = null, ?string $localeCode = null, bool $withDefault = false, bool $useCache = true): array
    {
        if (false === $useCache) {
            return $this->getUncachedSettingsByChannelAndLocale($channel, $localeCode, $withDefault);
        }

        return $this->monsieurbizSettingsCache->get(
            $this->getCacheKey($withDefault ? 'with_def' : 'no_def', $channel, $localeCode),
            function (ItemInterface $item) use ($channel, $localeCode, $withDefault): array {
                $item->tag($this->getCacheTags($channel, $localeCode));

                return $this->getUncachedSettingsByChannelAndLocale($channel, $localeCode, $withDefault);
            }
        );
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

    /**
     * @SuppressWarnings(PHPMD.BooleanArgumentFlag)
     */
    public function getSettingsValuesByChannelAndLocale(?ChannelInterface $channel = null, ?string $localeCode = null, bool $useCache = true): array
    {
        $allSettings = $this->getSettingsByChannelAndLocale($channel, $localeCode, false, $useCache);
        $settingsValues = [];
        /** @var SettingInterface $setting */
        foreach ($allSettings as $setting) {
            $settingsValues[$setting->getPath()] = $setting->getValue();
        }

        return $settingsValues;
    }

    public function getCurrentValue(?ChannelInterface $channel, ?string $localeCode, string $path): mixed
    {
        return $this->monsieurbizSettingsCache->get(
            $this->getCacheKey($path, $channel, $localeCode),
            function (ItemInterface $item) use ($channel, $localeCode, $path) {
                $item->tag($this->getCacheTags($channel, $localeCode));
                $settings = $this->getSettingsByChannelAndLocale($channel, $localeCode, true);
                if (isset($settings[$path])) {
                    return $settings[$path]->getValue();
                }

                return $this->getDefaultValue($path);
            }
        );
    }

    public function getDefaultValues(): array
    {
        return $this->metadata->getDefaultValues();
    }

    public function getDefaultValue(string $path): mixed
    {
        $defaultValues = $this->getDefaultValues();
        if (\array_key_exists($path, $defaultValues)) {
            return $defaultValues[$path];
        }

        return null;
    }

    /**
     * @inheritdoc
     */
    public function showLocalesInForm(): bool
    {
        return $this->metadata->useLocales();
    }

    private function getCacheKey(string $key, ?ChannelInterface $channel, ?string $localeCode): string
    {
        return implode('_', [
            $key,
            $this->getAlias(),
            $channel?->getCode() ?? self::DEFAULT_KEY,
            $localeCode ?? self::DEFAULT_KEY,
        ]);
    }

    private function getCacheTags(?ChannelInterface $channel, ?string $localeCode, array $extra = []): array
    {
        return array_merge([
            $this->getAlias(),
            \sprintf('vendor.%s', $this->getAliasAsArray()['vendor']),
            \sprintf('plugin.%s', $this->getAliasAsArray()['plugin']),
            \sprintf('channel.%s', $channel?->getCode() ?? self::DEFAULT_KEY),
            \sprintf('locale.%s', $localeCode ?? self::DEFAULT_KEY),
        ], $extra);
    }
}
