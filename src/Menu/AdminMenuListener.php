<?php

declare(strict_types=1);

namespace MonsieurBiz\SyliusSettingsPlugin\Menu;

use Knp\Menu\Util\MenuManipulator;
use Sylius\Bundle\UiBundle\Menu\Event\MenuBuilderEvent;

final class AdminMenuListener
{
    /**
     * @var MenuManipulator
     */
    private MenuManipulator $manipulator;

    /**
     * AdminMenuListener constructor.
     *
     * @param MenuManipulator $manipulator
     */
    public function __construct(MenuManipulator $manipulator)
    {
        $this->manipulator = $manipulator;
    }

    /**
     * @param MenuBuilderEvent $event
     */
    public function addAdminMenuItems(MenuBuilderEvent $event): void
    {
        $menu = $event->getMenu();
        $configurationMenu = $menu->getChild('configuration');
        $settings = $configurationMenu->addChild('monsieurbiz_settings', ['route' => 'monsieurbiz_sylius_settings_admin_index']);
        $settings
            ->setLabel('monsieurbiz.settings.menu.admin.configuration.settings')
            ->setLabelAttribute('icon', 'cog')
        ;
        $this->manipulator->moveChildToPosition($configurationMenu, $settings, 1);
    }
}
