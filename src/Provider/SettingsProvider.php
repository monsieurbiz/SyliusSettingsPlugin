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

use MonsieurBiz\SyliusSettingsPlugin\Exception\SettingsException;
use MonsieurBiz\SyliusSettingsPlugin\Settings\RegistryInterface;
use Sylius\Component\Channel\Context\ChannelContextInterface;
use Sylius\Component\Channel\Model\ChannelInterface;
use Sylius\Component\Locale\Context\LocaleContextInterface;

class SettingsProvider implements SettingsProviderInterface
{
    private RegistryInterface $settingsRegistry;

    private LocaleContextInterface $localeContext;

    private ChannelContextInterface $channelContext;

    public function __construct(
        RegistryInterface $settingsRegistry,
        ChannelContextInterface $channelContext,
        LocaleContextInterface $localeContext
    ) {
        $this->settingsRegistry = $settingsRegistry;
        $this->channelContext = $channelContext;
        $this->localeContext = $localeContext;
    }

    /**
     * @throws SettingsException
     *
     * @return mixed
     */
    public function getSettingValue(string $alias, string $path)
    {
        $this->getSettingValueByChannelAndLocale(
            $alias,
            $path,
            $this->channelContext->getChannel(),
            $this->localeContext->getLocaleCode()
        );
    }

    /**
     * @throws SettingsException
     *
     * @return mixed
     */
    public function getSettingValueByChannelAndLocale(
        string $alias,
        string $path,
        ChannelInterface $channel,
        ?string $locale = null
    ) {
        if ($settingsInstance = $this->settingsRegistry->getByAlias($alias)) {
            return $settingsInstance->getCurrentValue($channel, $locale, $path);
        }

        throw new SettingsException(sprintf('Cannot fetch setting %s - %s', $alias, $path));
    }
}
