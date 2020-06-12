<?php

declare(strict_types=1);

namespace MonsieurBiz\SyliusSettingsPlugin\Menu;

use Knp\Menu\FactoryInterface;
use Knp\Menu\ItemInterface;
use MonsieurBiz\SyliusSettingsPlugin\Settings\SettingsInterface;
use Sylius\Component\Core\Model\ChannelInterface;
use Sylius\Component\Channel\Repository\ChannelRepositoryInterface;

final class SettingsFormMenuBuilder
{
    /**
     * @var FactoryInterface
     */
    private FactoryInterface $factory;

    /**
     * @var ChannelRepositoryInterface
     */
    private ChannelRepositoryInterface $channelRepository;

    /**
     * SettingsFormMenuBuilder constructor.
     *
     * @param FactoryInterface $factory
     * @param ChannelRepositoryInterface $channelRepository
     */
    public function __construct(FactoryInterface $factory, ChannelRepositoryInterface $channelRepository)
    {
        $this->factory = $factory;
        $this->channelRepository = $channelRepository;
    }

    /**
     * @param array $options
     *
     * @return ItemInterface
     */
    public function createMenu(array $options = []): ItemInterface
    {
        $menu = $this->factory->createItem('root');

        if (!array_key_exists('settings', $options) || !$options['settings'] instanceof SettingsInterface) {
            return $menu;
        }

        $menu
            ->addChild('default')
            ->setAttribute('template', '@MonsieurBizSyliusSettingsPlugin/Crud/Edit/Tab/_default.html.twig')
            ->setLabel('monsieurbiz.settings.ui.by_default')
            ->setCurrent(true)
        ;

        /** @var ChannelInterface $channel */
        foreach ($this->channelRepository->findAll() as $channel) {
            $menu
                ->addChild('channel_' . $channel->getCode())
                ->setAttribute('template', '@MonsieurBizSyliusSettingsPlugin/Crud/Edit/Tab/_store.html.twig')
                ->setLabel($channel->getName())
                ->setExtra('channel', $channel)
            ;
        }

        return $menu;
    }
}
