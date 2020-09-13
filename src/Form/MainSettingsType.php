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

namespace MonsieurBiz\SyliusSettingsPlugin\Form;

use MonsieurBiz\SyliusSettingsPlugin\Settings\Settings;
use MonsieurBiz\SyliusSettingsPlugin\Settings\SettingsInterface;
use Sylius\Component\Channel\Repository\ChannelRepositoryInterface;
use Sylius\Component\Core\Model\ChannelInterface;
use Sylius\Component\Locale\Model\LocaleInterface;
use Sylius\Component\Resource\Repository\RepositoryInterface;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints as Assert;

final class MainSettingsType extends AbstractType implements MainSettingsTypeInterface
{
    /**
     * @var ChannelRepositoryInterface
     */
    private ChannelRepositoryInterface $channelRepository;

    /**
     * @var RepositoryInterface
     */
    private RepositoryInterface $localeRepository;

    /**
     * MainSettingsType constructor.
     *
     * @param ChannelRepositoryInterface $channelRepository
     * @param RepositoryInterface $localeRepository
     */
    public function __construct(
        ChannelRepositoryInterface $channelRepository,
        RepositoryInterface $localeRepository
    ) {
        $this->channelRepository = $channelRepository;
        $this->localeRepository = $localeRepository;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setRequired([
            'settings',
        ])->setAllowedTypes('settings', [SettingsInterface::class]);
    }

    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $settings = $options['settings'];
        $data = $options['data'];
        $builder->add(
            $key = Settings::DEFAULT_KEY . '-' . Settings::DEFAULT_KEY, $settings->getFormClass(), [
                'settings' => $settings,
                'channel' => null,
                'label' => false,
                'show_default_checkboxes' => false,
                'data' => $data[$key] ?? null,
                'constraints' => [
                    new Assert\Valid(),
                ],
            ]);

        /** @var LocaleInterface $locale */
        foreach ($this->localeRepository->findAll() as $locale) {
            $builder->add(
                $key = Settings::DEFAULT_KEY . '-' . $locale->getCode(), $settings->getFormClass(), [
                    'settings' => $settings,
                    'channel' => null,
                    'label' => false,
                    'show_default_checkboxes' => true,
                    'data' => $data[$key] ?? null,
                    'constraints' => [
                        new Assert\Valid(),
                    ],
                ]);
        }

        /** @var ChannelInterface $channel */
        foreach ($this->channelRepository->findAll() as $channel) {
            $builder->add(
                $key = 'channel-' . $channel->getId() . '-' . Settings::DEFAULT_KEY, $settings->getFormClass(), [
                    'settings' => $settings,
                    'channel' => $channel,
                    'label' => false,
                    'show_default_checkboxes' => true,
                    'data' => $data[$key] ?? null,
                    'constraints' => [
                        new Assert\Valid(),
                    ],
                ]);

            foreach ($channel->getLocales() as $locale) {
                $builder->add(
                    $key = 'channel-' . $channel->getId() . '-' . $locale->getCode(), $settings->getFormClass(), [
                        'settings' => $settings,
                        'channel' => $channel,
                        'label' => false,
                        'show_default_checkboxes' => true,
                        'data' => $data[$key] ?? null,
                        'constraints' => [
                            new Assert\Valid(),
                        ],
                    ]);
            }
        }

        $builder->addEventListener(FormEvents::PRE_SET_DATA, function (FormEvent $event): void {
            // Disable fields without value
            // and Enable default checkboxes
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
