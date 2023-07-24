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

namespace MonsieurBiz\SyliusSettingsPlugin\Command;

use Doctrine\ORM\EntityManagerInterface;
use Exception;
use MonsieurBiz\SyliusSettingsPlugin\Entity\Setting\SettingInterface;
use MonsieurBiz\SyliusSettingsPlugin\Formatter\SettingsFormatterInterface;
use MonsieurBiz\SyliusSettingsPlugin\Repository\SettingRepositoryInterface;
use MonsieurBiz\SyliusSettingsPlugin\Settings\RegistryInterface;
use MonsieurBiz\SyliusSettingsPlugin\Settings\SettingsInterface;
use Sylius\Component\Channel\Repository\ChannelRepositoryInterface;
use Sylius\Component\Core\Model\ChannelInterface;
use Sylius\Component\Resource\Factory\FactoryInterface;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

class SetSettingsCommand extends Command
{
    private const ARGUMENT_ALIAS = 'alias';

    private const ARGUMENT_PATH = 'path';

    private const OPTION_CHANNEL = 'channel';

    private const OPTION_LOCALE = 'locale';

    private const ARGUMENT_TYPE = 'type';

    private const ARGUMENT_VALUE = 'value';

    private RegistryInterface $settingsRegistry;

    private ChannelRepositoryInterface $channelRepository;

    private FactoryInterface $settingFactory;

    private SettingRepositoryInterface $settingRepository;

    private EntityManagerInterface $settingManager;

    private SettingsFormatterInterface $settingsFormatter;

    protected static $defaultName = 'monsieurbiz:settings:set';

    public function __construct(
        RegistryInterface $settingsRegistry,
        ChannelRepositoryInterface $channelRepository,
        FactoryInterface $settingFactory,
        SettingRepositoryInterface $settingRepository,
        EntityManagerInterface $settingManager,
        SettingsFormatterInterface $settingsFormatter,
        string $name = null
    ) {
        $this->settingsRegistry = $settingsRegistry;
        $this->channelRepository = $channelRepository;
        $this->settingFactory = $settingFactory;
        $this->settingRepository = $settingRepository;
        $this->settingManager = $settingManager;
        $this->settingsFormatter = $settingsFormatter;
        parent::__construct($name);
    }

    protected function configure(): void
    {
        $this
            ->setDescription('Set a settings value for a given path')
            ->setHelp('This command allows you to set a settings value for a given path')
            ->addArgument(self::ARGUMENT_ALIAS, InputArgument::REQUIRED, 'Alias of the settings like {vendor}.{plugin} from the setting definition')
            ->addArgument(self::ARGUMENT_PATH, InputArgument::REQUIRED, 'Path of the settings')
            ->addArgument(self::ARGUMENT_TYPE, InputArgument::REQUIRED, 'Type of the settings', null)
            ->addArgument(self::ARGUMENT_VALUE, InputArgument::REQUIRED, 'Value of the settings')
            ->addOption(self::OPTION_CHANNEL, 'c', InputOption::VALUE_OPTIONAL, 'Channel code')
            ->addOption(self::OPTION_LOCALE, 'l', InputOption::VALUE_OPTIONAL, 'Locale code')
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        try {
            /** @var string $alias */
            $alias = $input->getArgument(self::ARGUMENT_ALIAS);
            /** @var string $path */
            $path = $input->getArgument(self::ARGUMENT_PATH);
            $channelCode = $input->getOption(self::OPTION_CHANNEL);
            /** @var ?string $locale */
            $locale = $input->getOption(self::OPTION_LOCALE);

            $channel = null;
            if (null !== $channelCode) {
                /** @var ?ChannelInterface $channel */
                $channel = $this->channelRepository->findOneBy(['code' => $channelCode]);
            }

            /** @var SettingsInterface $settings */
            $settings = $this->settingsRegistry->getByAlias($alias);
            ['vendor' => $vendor, 'plugin' => $plugin] = $settings->getAliasAsArray();
            $setting = $this->getSetting($vendor, $plugin, $path, $locale, $channel);

            /** @var string $type */
            $type = $input->getArgument(self::ARGUMENT_TYPE);
            $this->validateType($type);

            $value = $input->getArgument(self::ARGUMENT_VALUE);

            $setting->setStorageType($type);
            /** @phpstan-ignore-next-line */
            $setting->setValue($this->settingsFormatter->formatValue($type, $value));

            $this->settingManager->persist($setting);
            $this->settingManager->flush();
        } catch (Exception $e) {
            $output->writeln(sprintf('<error>%s</error>', $e->getMessage()));

            return Command::FAILURE;
        }

        $output->writeln(sprintf('<info>%s</info>', 'The setting has been saved'));

        return Command::SUCCESS;
    }

    private function getSetting(string $vendor, string $plugin, ?string $path, ?string $locale, ?ChannelInterface $channel): SettingInterface
    {
        /** @var SettingInterface|null $setting */
        $setting = $this->settingRepository->findOneBy([
            'vendor' => $vendor,
            'plugin' => $plugin,
            'path' => $path,
            'localeCode' => $locale,
            'channel' => $channel,
        ]);

        // Reset existing value
        if ($setting) {
            $setting->setValue(null);
        }

        if (null === $setting) {
            /** @var SettingInterface $setting */
            $setting = $this->settingFactory->createNew();
            $setting->setVendor($vendor);
            $setting->setPlugin($plugin);
            $setting->setPath($path);
            $setting->setLocaleCode($locale);
            $setting->setChannel($channel);
        }

        return $setting;
    }

    private function validateType(string $type): void
    {
        $types = [
            SettingInterface::STORAGE_TYPE_TEXT,
            SettingInterface::STORAGE_TYPE_BOOLEAN,
            SettingInterface::STORAGE_TYPE_INTEGER,
            SettingInterface::STORAGE_TYPE_FLOAT,
            SettingInterface::STORAGE_TYPE_DATETIME,
            SettingInterface::STORAGE_TYPE_DATE,
            SettingInterface::STORAGE_TYPE_JSON,
        ];

        if (!\in_array($type, $types, true)) {
            throw new Exception(sprintf('The type "%s" is not valid. Valid types are: %s', $type, implode(', ', $types)));
        }
    }
}
