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

namespace MonsieurBiz\SyliusSettingsPlugin\Menu;

use Knp\Menu\Util\MenuManipulator;
use MonsieurBiz\SyliusSettingsPlugin\Settings\RegistryInterface;
use Sylius\Bundle\UiBundle\Menu\Event\MenuBuilderEvent;

final class AdminMenuListener
{
    private MenuManipulator $manipulator;

    private RegistryInterface $settingsRegistry;

    /**
     * AdminMenuListener constructor.
     */
    public function __construct(MenuManipulator $manipulator, RegistryInterface $settingsRegistry)
    {
        $this->manipulator = $manipulator;
        $this->settingsRegistry = $settingsRegistry;
    }

    public function addAdminMenuItems(MenuBuilderEvent $event): void
    {
        if ($this->settingsRegistry->count()) {
            $menu = $event->getMenu();
            if (null !== ($configurationMenu = $menu->getChild('configuration'))) {
                $settings = $configurationMenu->addChild('monsieurbiz_settings', ['route' => 'monsieurbiz_sylius_settings_admin_index']);
                $settings
                    ->setLabel('monsieurbiz.settings.menu.admin.configuration.settings')
                    ->setLabelAttribute('icon', 'cog')
                ;
                $this->manipulator->moveChildToPosition($configurationMenu, $settings, 1);
            }
        }
    }
}
