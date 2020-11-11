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
    /**
     * @var FormFactoryInterface
     */
    private FormFactoryInterface $formFactory;

    /**
     * @var ChannelRepositoryInterface
     */
    private ChannelRepositoryInterface $channelRepository;

    /**
     * @var RepositoryInterface
     */
    private RepositoryInterface $localeRepository;

    /**
     * MainSettingsFormTypeFactory constructor.
     *
     * @param FormFactoryInterface $formFactory
     * @param ChannelRepositoryInterface $channelRepository
     * @param RepositoryInterface $localeRepository
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

    /**
     * @param SettingsInterface $settings
     * @param string $type
     * @param array $options
     *
     * @return FormInterface
     */
    public function createNew(SettingsInterface $settings, string $type, array $options = []): FormInterface
    {
        return $this->formFactory->create(
            $type,
            $this->getInitialFormData($settings),
            $options
        );
    }

    /**
     * @param SettingsInterface $settings
     *
     * @return array
     */
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

        /** @var ChannelInterface $channel */
        foreach ($this->channelRepository->findAll() as $channel) {
            $data['channel-' . $channel->getId() . '-' . Settings::DEFAULT_KEY] = $settings->getSettingsValuesByChannelAndLocale($channel);

            if ($settings->showLocalesInForm()) {
                foreach ($channel->getLocales() as $locale) {
                    $data['channel-' . $channel->getId() . '-' . $locale->getCode()] = $settings->getSettingsValuesByChannelAndLocale($channel, $locale->getCode());
                }
            }
        }

        return $data;
    }
}
