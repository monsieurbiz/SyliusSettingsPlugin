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

use DateTime;
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
    ) {
        $this->settingsRegistry = $settingsRegistry;
        $this->channelRepository = $channelRepository;
        $this->settingFactory = $settingFactory;
        $this->optionsResolver = new OptionsResolver();

        $this->configureOptions($this->optionsResolver);
    }

    public function create(array $options = []): SettingInterface
    {
        $options = $this->optionsResolver->resolve($options);

        /** @var SettingsInterface $settings */
        $settings = $this->settingsRegistry->getByAlias($options['alias']);
        ['vendor' => $vendor, 'plugin' => $plugin] = $settings->getAliasAsArray();

        /** @var SettingInterface $setting */
        $setting = $this->settingFactory->createNew();
        $setting->setVendor($vendor);
        $setting->setPlugin($plugin);
        $setting->setPath($options['path']);
        $setting->setLocaleCode($options['locale']);
        $setting->setStorageType($options['type']);

        $this->formatValue($options['type'], $options['value']);
        $setting->setValue($options['value']);

        if (null !== $options['channel']) {
            /** @var ?ChannelInterface $channel */
            $channel = $this->channelRepository->findOneBy(['code' => $options['channel']]);
            $setting->setChannel($channel);
        }

        return $setting;
    }

    /**
     * @param int|float|string|array $value
     *
     * @SuppressWarnings(PHPMD.CyclomaticComplexity)
     *
     * @phpstan-ignore-next-line
     */
    private function formatValue(string $type, &$value): void
    {
        switch ($type) {
            case SettingInterface::STORAGE_TYPE_BOOLEAN:
                $value = (bool) $value;

                break;
            case SettingInterface::STORAGE_TYPE_INTEGER:
                $value = (int) $value;

                break;
            case SettingInterface::STORAGE_TYPE_FLOAT:
                $value = (float) $value;

                break;
            case SettingInterface::STORAGE_TYPE_JSON:
                if (!\is_array($value)) {
                    $value = json_decode((string) $value, true);
                }

                break;
            case SettingInterface::STORAGE_TYPE_DATE:
            case SettingInterface::STORAGE_TYPE_DATETIME:
                if (\is_int($value)) {
                    $value = (new DateTime())->setTimestamp($value);

                    break;
                }

                $value = new DateTime((string) $value);

                break;
        }
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
        ;
    }
}
