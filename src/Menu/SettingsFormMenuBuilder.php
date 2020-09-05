<?php

declare(strict_types=1);

namespace MonsieurBiz\SyliusSettingsPlugin\Menu;

use Knp\Menu\FactoryInterface;
use Knp\Menu\ItemInterface;
use MonsieurBiz\SyliusSettingsPlugin\Settings\SettingsInterface;
use Sylius\Component\Core\Model\ChannelInterface;
use Sylius\Component\Channel\Repository\ChannelRepositoryInterface;
use Sylius\Component\Resource\Repository\RepositoryInterface;

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
     * @var RepositoryInterface
     */
    private RepositoryInterface $localeRepository;

    /**
     * SettingsFormMenuBuilder constructor.
     *
     * @param FactoryInterface $factory
     * @param ChannelRepositoryInterface $channelRepository
     * @param RepositoryInterface $localeRepository
     */
    public function __construct(
        FactoryInterface $factory,
        ChannelRepositoryInterface $channelRepository,
        RepositoryInterface $localeRepository
    ) {
        $this->factory = $factory;
        $this->channelRepository = $channelRepository;
        $this->localeRepository = $localeRepository;
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
            ->setExtra('locales', $this->localeRepository->findAll())
        ;

        /** @var ChannelInterface $channel */
        foreach ($this->channelRepository->findAll() as $channel) {
            $menu
                ->addChild('channel_' . $channel->getCode())
                ->setAttribute('template', '@MonsieurBizSyliusSettingsPlugin/Crud/Edit/Tab/_store.html.twig')
                ->setLabel($channel->getName())
                ->setExtra('channel', $channel)
                ->setExtra('locales', $channel->getLocales())
            ;
        }

        return $menu;
    }
}
