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

namespace MonsieurBiz\SyliusSettingsPlugin\Fixture\Factory;

use MonsieurBiz\SyliusSettingsPlugin\Entity\Setting\Setting;
use MonsieurBiz\SyliusSettingsPlugin\Entity\Setting\SettingInterface;
use MonsieurBiz\SyliusSettingsPlugin\Formatter\SettingsFormatterInterface;
use MonsieurBiz\SyliusSettingsPlugin\Provider\SettingProviderInterface;
use MonsieurBiz\SyliusSettingsPlugin\Settings\RegistryInterface;
use MonsieurBiz\SyliusSettingsPlugin\Settings\SettingsInterface;
use Sylius\Bundle\CoreBundle\Fixture\Factory\AbstractExampleFactory;
use Sylius\Component\Channel\Repository\ChannelRepositoryInterface;
use Sylius\Component\Core\Model\ChannelInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class SettingsFixtureFactory extends AbstractExampleFactory
{
    private RegistryInterface $settingsRegistry;

    private OptionsResolver $optionsResolver;

    private ChannelRepositoryInterface $channelRepository;

    private SettingsFormatterInterface $settingsFormatter;

    private SettingProviderInterface $settingProvider;

    public function __construct(
        RegistryInterface $settingsRegistry,
        ChannelRepositoryInterface $channelRepository,
        SettingsFormatterInterface $settingsFormatter,
        SettingProviderInterface $settingProvider
    ) {
        $this->settingsRegistry = $settingsRegistry;
        $this->channelRepository = $channelRepository;
        $this->settingsFormatter = $settingsFormatter;
        $this->settingProvider = $settingProvider;
        $this->optionsResolver = new OptionsResolver();

        $this->configureOptions($this->optionsResolver);
    }

    public function create(array $options = []): SettingInterface
    {
        $options = $this->optionsResolver->resolve($options);

        /** @var SettingsInterface $settings */
        $settings = $this->settingsRegistry->getByAlias($options['alias']);
        ['vendor' => $vendor, 'plugin' => $plugin] = $settings->getAliasAsArray();

        $channel = null;
        if (null !== $options['channel']) {
            /** @var ?ChannelInterface $channel */
            $channel = $this->channelRepository->findOneBy(['code' => $options['channel']]);
        }
        $setting = $this->settingProvider->getSettingOrCreateNew($vendor, $plugin, $options['path'], $options['locale'], $channel);

        // If it has a type the value was already existing
        if ($options['ignore_if_exists'] && null !== $setting->getStorageType()) {
            return $setting;
        }

        $this->settingProvider->resetExistingValue($setting);
        $setting->setStorageType($options['type']); // If the type has changed, we change it!
        $setting->setValue($this->settingsFormatter->formatValue($options['type'], $options['value']));

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
            ->setDefault('type', SettingInterface::STORAGE_TYPE_TEXT)
            ->setAllowedTypes('type', 'string')
            ->setAllowedValues('type', Setting::getAllStorageTypes())
            ->setDefault('value', null)
            ->setAllowedTypes('value', ['null', 'string', 'integer', 'bool', 'float', 'Datetime', 'array'])
            ->setDefault('ignore_if_exists', false)
            ->setAllowedTypes('ignore_if_exists', 'bool')
        ;
    }
}
