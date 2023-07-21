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

namespace MonsieurBiz\SyliusSettingsPlugin\Factory\Form;

use MonsieurBiz\SyliusSettingsPlugin\Settings\Settings;
use MonsieurBiz\SyliusSettingsPlugin\Settings\SettingsInterface;
use Sylius\Component\Channel\Repository\ChannelRepositoryInterface;
use Sylius\Component\Core\Model\ChannelInterface;
use Sylius\Component\Locale\Model\LocaleInterface;
use Sylius\Component\Resource\Repository\RepositoryInterface;
use Symfony\Component\Form\FormFactoryInterface;
use Symfony\Component\Form\FormInterface;

final class MainSettingsFormTypeFactory implements MainSettingsFormTypeFactoryInterface
{
    private FormFactoryInterface $formFactory;

    private ChannelRepositoryInterface $channelRepository;

    private RepositoryInterface $localeRepository;

    /**
     * MainSettingsFormTypeFactory constructor.
     */
    public function __construct(
        FormFactoryInterface $formFactory,
        ChannelRepositoryInterface $channelRepository,
        RepositoryInterface $localeRepository
    ) {
        $this->formFactory = $formFactory;
        $this->channelRepository = $channelRepository;
        $this->localeRepository = $localeRepository;
    }

    public function createNew(SettingsInterface $settings, string $type, array $options = []): FormInterface
    {
        return $this->formFactory->create(
            $type,
            $this->getInitialFormData($settings),
            $options
        );
    }

    private function getInitialFormData(SettingsInterface $settings): array
    {
        $data = [
            Settings::DEFAULT_KEY . '-' . Settings::DEFAULT_KEY => $settings->getSettingsValuesByChannelAndLocale() + $settings->getDefaultValues(),
        ];

        if ($settings->showLocalesInForm()) {
            /** @var LocaleInterface $locale */
            foreach ($this->localeRepository->findAll() as $locale) {
                $data[Settings::DEFAULT_KEY . '-' . $locale->getCode()] = $settings->getSettingsValuesByChannelAndLocale(null, $locale->getCode());
            }
        }

        return $data + $this->getChannelInitialFormData($settings);
    }

    /**
     * @SuppressWarnings(PHPMD.CyclomaticComplexity)
     */
    private function getChannelInitialFormData(SettingsInterface $settings): array
    {
        $data = [];
        /** @var ChannelInterface $channel */
        foreach ($this->channelRepository->findAll() as $channel) {
            $channelKey = 'channel-' . $channel->getId() . '-' . Settings::DEFAULT_KEY;
            $data[$channelKey] = $settings->getSettingsValuesByChannelAndLocale($channel);
            $defaultDataByChannel = $this->getDefaultValuesByChannel($settings, $channel);

            if (isset($defaultDataByChannel[$channelKey])) {
                $data[$channelKey] += $defaultDataByChannel[$channelKey];
            }

            if ($settings->showLocalesInForm()) {
                foreach ($channel->getLocales() as $locale) {
                    $data['channel-' . $channel->getId() . '-' . $locale->getCode()] = $settings->getSettingsValuesByChannelAndLocale($channel, $locale->getCode());
                }
            }
        }

        return $data;
    }

    private function getDefaultValuesByChannel(SettingsInterface $settings, ChannelInterface $channel): array
    {
        $defaultDataByChannel = [];
        $defaultValuesForChannels = $settings->getDefaultValuesForChannels();
        $channelCode = $channel->getCode();
        if (isset($defaultValuesForChannels[$channelCode])) {
            $defaultDataByChannel['channel-' . $channel->getId() . '-' . Settings::DEFAULT_KEY] = $defaultValuesForChannels[$channelCode]['default_values'] ?? [];
        }

        return $defaultDataByChannel;
    }
}
