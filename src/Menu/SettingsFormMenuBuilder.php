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

namespace MonsieurBiz\SyliusSettingsPlugin\Menu;

use Knp\Menu\FactoryInterface;
use Knp\Menu\ItemInterface;
use MonsieurBiz\SyliusSettingsPlugin\Settings\SettingsInterface;
use Sylius\Component\Channel\Repository\ChannelRepositoryInterface;
use Sylius\Component\Core\Model\ChannelInterface;
use Sylius\Component\Resource\Repository\RepositoryInterface;

final class SettingsFormMenuBuilder
{
    /**
     * SettingsFormMenuBuilder constructor.
     */
    public function __construct(
        private FactoryInterface $factory,
        private ChannelRepositoryInterface $channelRepository,
        private RepositoryInterface $localeRepository
    ) {
    }

    public function createMenu(array $options = []): ItemInterface
    {
        $menu = $this->factory->createItem('root');

        if (!\array_key_exists('settings', $options) || !$options['settings'] instanceof SettingsInterface) {
            return $menu;
        }

        $menu
            ->addChild('default')
            ->setAttribute('template', '@MonsieurBizSyliusSettingsPlugin/admin/settings/edit/content/form/sections/default.html.twig')
            ->setLabel('monsieurbiz.settings.ui.by_default')
            ->setCurrent(true)
            ->setExtra('settings', $options['settings'])
            ->setExtra('locales', $this->localeRepository->findAll())
        ;

        /** @var ChannelInterface $channel */
        foreach ($this->channelRepository->findAll() as $channel) {
            $menu
                ->addChild('channel_' . $channel->getCode())
                ->setAttribute('template', '@MonsieurBizSyliusSettingsPlugin/admin/settings/edit/content/form/sections/store.html.twig')
                ->setLabel($channel->getName())
                ->setExtra('settings', $options['settings'])
                ->setExtra('channel', $channel)
                ->setExtra('locales', $channel->getLocales())
            ;
        }

        return $menu;
    }
}
