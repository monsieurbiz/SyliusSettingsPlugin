<?php

declare(strict_types=1);

namespace MonsieurBiz\SyliusSettingsPlugin\Twig\Extension;

use MonsieurBiz\SyliusSettingsPlugin\Settings\RegistryInterface;
use Sylius\Component\Channel\Context\ChannelContextInterface;
use Sylius\Component\Core\Context\ShopperContext;
use Sylius\Component\Locale\Context\LocaleContextInterface;
use Twig\Environment;
use Twig\Extension\AbstractExtension;
use Twig\Extension\ExtensionInterface;
use Twig\TwigFunction;

final class SettingsExtension extends AbstractExtension implements ExtensionInterface
{
    /**
     * @var RegistryInterface
     */
    private RegistryInterface $settingsRegistry;

    /**
     * @var LocaleContextInterface
     */
    private LocaleContextInterface $localeContext;

    /**
     * @var ChannelContextInterface
     */
    private ChannelContextInterface $channelContext;

    public function __construct(
        RegistryInterface $settingsRegistry,
        LocaleContextInterface $localeContext,
        ChannelContextInterface $channelContext
    ) {
        $this->settingsRegistry = $settingsRegistry;
        $this->localeContext = $localeContext;
        $this->channelContext = $channelContext;
    }

    public function getFunctions()
    {
        return [
            new TwigFunction('setting', [$this, 'getSettingValue'], [
                'needs_context' => true,
            ]),
        ];
    }

    /**
     * @param array $context
     * @param string $alias
     * @param string $path
     *
     * @return mixed
     */
    public function getSettingValue(array $context, string $alias, string $path)
    {
        if (isset($context['sylius']) && $context['sylius'] instanceof ShopperContext) {
            if ($settingsInstance = $this->settingsRegistry->getByAlias($alias)) {
                return $settingsInstance->getCurrentValue(
                    $context['sylius']->getChannel(),
                    $context['sylius']->getLocaleCode(),
                    $path
                );
            }
        }
        return null;
    }
}
