<?php

declare(strict_types=1);

namespace MonsieurBiz\SyliusSettingsPlugin\Factory;

use MonsieurBiz\SyliusSettingsPlugin\Entity\Setting\SettingInterface;
use MonsieurBiz\SyliusSettingsPlugin\Settings\SettingsInterface;
use Sylius\Component\Core\Model\ChannelInterface;
use Sylius\Component\Locale\Model\LocaleInterface;

class SettingFactory implements SettingFactoryInterface
{
    /**
     * @var string
     */
    private $className;

    /**
     * @param string $className
     */
    public function __construct(string $className)
    {
        $this->className = $className;
    }

    /**
     * @return SettingInterface
     */
    public function createNew()
    {
        return new $this->className();
    }

    /**
     * @inheritDoc
     */
    public function createNewFromGlobalSettings(SettingsInterface $settings, ?ChannelInterface $channel, ?LocaleInterface $locale): SettingInterface
    {
        $aliases = $settings->getAliasAsArray();

        $setting = $this->createNew();
        $setting->setChannel($channel);
        $setting->setLocaleCode(null === $locale ? null : $locale->getCode());
        $setting->setVendor($aliases['vendor']);
        $setting->setPlugin($aliases['plugin']);

        return $setting;
    }
}
