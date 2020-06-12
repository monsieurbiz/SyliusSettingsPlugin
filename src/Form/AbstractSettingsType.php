<?php

declare(strict_types=1);

namespace MonsieurBiz\SyliusSettingsPlugin\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\OptionsResolver\OptionsResolver;

abstract class AbstractSettingsType extends AbstractType implements SettingsTypeInterface
{
    /**
     * {@inheritdoc}
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'settings' => null,
            'channel' => null,
        ]);
    }
}
