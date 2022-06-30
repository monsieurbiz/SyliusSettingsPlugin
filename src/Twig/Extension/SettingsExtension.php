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

namespace MonsieurBiz\SyliusSettingsPlugin\Twig\Extension;

use MonsieurBiz\SyliusSettingsPlugin\Exception\SettingsException;
use MonsieurBiz\SyliusSettingsPlugin\Provider\SettingsProviderInterface;
use Sylius\Component\Core\Context\ShopperContextInterface;
use Twig\Extension\AbstractExtension;
use Twig\Extension\ExtensionInterface;
use Twig\TwigFunction;

final class SettingsExtension extends AbstractExtension implements ExtensionInterface
{
    private SettingsProviderInterface $settingsProvider;

    public function __construct(SettingsProviderInterface $settings)
    {
        $this->settingsProvider = $settings;
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
     * @return mixed
     */
    public function getSettingValue(array $context, string $alias, string $path)
    {
        if (isset($context['sylius']) && $context['sylius'] instanceof ShopperContextInterface) {
            try {
                $this->settingsProvider->getSettingValue($alias, $path);
            } catch (SettingsException $settingsException) {
                return null;
            }
        }

        return null;
    }
}
