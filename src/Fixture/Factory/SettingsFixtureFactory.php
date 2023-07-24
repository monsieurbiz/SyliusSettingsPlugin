<?php

/*
 * This file is part of SyliusSettingsPlugin corporate website.
 *
 * (c) SyliusSettingsPlugin <sylius+syliussettingsplugin@monsieurbiz.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

declare(strict_types=1);

namespace MonsieurBiz\SyliusSettingsPlugin\Fixture\Factory;

use MonsieurBiz\SyliusSettingsPlugin\Entity\Setting\SettingInterface;
use MonsieurBiz\SyliusSettingsPlugin\Settings\RegistryInterface;
use MonsieurBiz\SyliusSettingsPlugin\Settings\SettingsInterface;
use Sylius\Bundle\CoreBundle\Fixture\Factory\AbstractExampleFactory;
use Sylius\Component\Channel\Repository\ChannelRepositoryInterface;
use Sylius\Component\Core\Model\ChannelInterface;
use Sylius\Component\Resource\Factory\FactoryInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;


class SettingsFixtureFactory extends AbstractExampleFactory
{
    private RegistryInterface $settingsRegistry;
    private OptionsResolver $optionsResolver;
    private ChannelRepositoryInterface $channelRepository;
    private FactoryInterface $settingFactory;

    public function __construct(
        RegistryInterface $settingsRegistry,
        ChannelRepositoryInterface $channelRepository,
        FactoryInterface $settingFactory
    )
    {
        $this->settingsRegistry = $settingsRegistry;
        $this->channelRepository = $channelRepository;
        $this->settingFactory = $settingFactory;
        $this->optionsResolver = new OptionsResolver();
    }

    public function create(array $options = []): SettingInterface
    {
        $options = $this->optionsResolver->resolve($options);

        /** @var SettingsInterface $settings */
        $settings = $this->settingsRegistry->getByAlias($options['alias']);
        [$vendor, $plugin] = $settings->getAliasAsArray();

        /** @var $setting SettingInterface */
        $setting = $this->settingFactory->createNew();
        $setting->setVendor($vendor);
        $setting->setPlugin($plugin);
        $setting->setPath($options['path']);
        $setting->setStorageType($options['type']);
        $setting->setValue($options['value']);
        $setting->setLocaleCode($options['locale']);

        if (null !== $options['channel']) {
            /** @var ?ChannelInterface $channel */
            $channel = $this->channelRepository->findOneBy(['code' => $options['channel']]);
            $setting->setChannel($channel);
        }

        return $setting;
    }

    protected function configureOptions(OptionsResolver $resolver): void
    {
        $resolver
            ->setDefault('alias', '')
            ->setAllowedTypes('alias', 'string')
            ->setDefault('path', '')
            ->setAllowedTypes('path', 'string')
            ->setDefault('channel', null)
            ->setAllowedTypes('channel', ['null', 'string'])
            ->setDefault('locale', null)
            ->setAllowedTypes('locale', ['null', 'string'])
            ->setDefault('type', 'text')
            ->setAllowedTypes('type', 'string')
            ->setDefault('value', null)
        ;
    }
}
