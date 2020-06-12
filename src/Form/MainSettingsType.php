<?php

declare(strict_types=1);

namespace MonsieurBiz\SyliusSettingsPlugin\Form;

use MonsieurBiz\SyliusSettingsPlugin\Settings\SettingsInterface;
use Sylius\Component\Channel\Repository\ChannelRepositoryInterface;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;

class MainSettingsType extends AbstractType implements MainSettingsTypeInterface
{
    /**
     * @var ChannelRepositoryInterface
     */
    private ChannelRepositoryInterface $channelRepository;

    public function __construct(ChannelRepositoryInterface $channelRepository)
    {
        $this->channelRepository = $channelRepository;
    }

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder->addEventListener(FormEvents::PRE_SET_DATA, function(FormEvent $event) {
            $form = $event->getForm();
            /** @var SettingsInterface $settings */
            $settings = $event->getData();

            $form->add('default', $settings->getFormClass(), [
                'settings' => $settings,
                'mapped' => false,
                'label' => false,
                'data' => $settings,
            ]);

            foreach ($this->channelRepository->findAll() as $channel) {
                $form->add('channel_' . $channel->getId(), $settings->getFormClass(), [
                    'settings' => $settings,
                    'channel' => $channel,
                    'mapped' => false,
                    'label' => false,
                    'data' => $settings,
                ]);
            }
        });
    }

    /**
     * {@inheritdoc}
     */
    public function getBlockPrefix(): string
    {
        return 'settings';
    }
}
