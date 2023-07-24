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

use MonsieurBiz\SyliusSettingsPlugin\Entity\Setting\SettingInterface;
use MonsieurBiz\SyliusSettingsPlugin\Formatter\SettingsFormatterInterface;
use MonsieurBiz\SyliusSettingsPlugin\Repository\SettingRepositoryInterface;
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

    private SettingRepositoryInterface $settingRepository;

    private SettingsFormatterInterface $settingsFormatter;

    public function __construct(
        RegistryInterface $settingsRegistry,
        ChannelRepositoryInterface $channelRepository,
        FactoryInterface $settingFactory,
        SettingRepositoryInterface $settingRepository,
        SettingsFormatterInterface $settingsFormatter
    ) {
        $this->settingsRegistry = $settingsRegistry;
        $this->channelRepository = $channelRepository;
        $this->settingFactory = $settingFactory;
        $this->settingRepository = $settingRepository;
        $this->settingsFormatter = $settingsFormatter;
        $this->optionsResolver = new OptionsResolver();

        $this->configureOptions($this->optionsResolver);
    }

    public function create(array $options = []): SettingInterface
    {
        $options = $this->optionsResolver->resolve($options);

        /** @var SettingsInterface $settings */
        $settings = $this->settingsRegistry->getByAlias($options['alias']);
        ['vendor' => $vendor, 'plugin' => $plugin] = $settings->getAliasAsArray();

        /** @var SettingInterface|null $setting */
        $setting = $this->settingRepository->findOneBy([
            'vendor' => $vendor,
            'plugin' => $plugin,
            'path' => $options['path'],
            'localeCode' => $options['locale'],
            'channel' => $options['channel'],
        ]);

        if (null === $setting) {
            /** @var SettingInterface $setting */
            $setting = $this->settingFactory->createNew();
            $setting->setVendor($vendor);
            $setting->setPlugin($plugin);
            $setting->setPath($options['path']);
            $setting->setLocaleCode($options['locale']);
            if (null !== $options['channel']) {
                /** @var ?ChannelInterface $channel */
                $channel = $this->channelRepository->findOneBy(['code' => $options['channel']]);
                $setting->setChannel($channel);
            }
            $setting->setStorageType($options['type']); // Do it once for the reset of the previous value to work
        } elseif ($options['ignore_if_exists']) {
            return $setting;
        }

        $setting->setValue(null); // reset the previous value according to the potential previous type
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
            ->setAllowedValues('type', [SettingInterface::STORAGE_TYPE_TEXT, SettingInterface::STORAGE_TYPE_BOOLEAN, SettingInterface::STORAGE_TYPE_INTEGER,
                SettingInterface::STORAGE_TYPE_FLOAT, SettingInterface::STORAGE_TYPE_JSON, SettingInterface::STORAGE_TYPE_DATE, SettingInterface::STORAGE_TYPE_DATETIME])
            ->setDefault('value', null)
            ->setAllowedTypes('value', ['null', 'string', 'integer', 'bool', 'float', 'Datetime', 'array'])
            ->setDefault('ignore_if_exists', false)
            ->setAllowedTypes('ignore_if_exists', 'bool')
        ;
    }
}
