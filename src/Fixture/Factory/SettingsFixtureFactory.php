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

    public function __construct(
        RegistryInterface $settingsRegistry,
        ChannelRepositoryInterface $channelRepository,
        FactoryInterface $settingFactory,
        SettingRepositoryInterface $settingRepository
    ) {
        $this->settingsRegistry = $settingsRegistry;
        $this->channelRepository = $channelRepository;
        $this->settingFactory = $settingFactory;
        $this->settingRepository = $settingRepository;
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
        }

        $setting->setValue(null); // reset the previous value according to the potential previous type
        $setting->setStorageType($options['type']);
        $setting->setValue($this->formatValue($options['type'], $options['value']));

        return $setting;
    }

    /**
     * @param int|float|string|array $value
     * @param mixed $type
     *
     * @return mixed
     *
     * @SuppressWarnings(PHPMD.CyclomaticComplexity)
     */
    private function formatValue($type, $value)
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

                /** @phpstan-ignore-next-line */
                $value = new DateTime((string) $value);

                break;
        }

        return $value;
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
