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

namespace App\Form;

use MonsieurBiz\SyliusSettingsPlugin\Form\AbstractSettingsType;
use MonsieurBiz\SyliusSettingsPlugin\Form\SettingsTypeInterface;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\UX\LiveComponent\Form\Type\LiveCollectionType;

class SettingsType extends AbstractSettingsType implements SettingsTypeInterface
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        if ($this->isDefaultForm($builder)) {
            $this->addWithDefaultCheckbox(
                $builder,
                'demo_message',
                TextType::class,
                [
                    'required' => true,
                    'constraints' => [
                        new Assert\NotBlank(),
                    ],
                ]
            );
        } else {
            $this->addWithDefaultCheckbox(
                $builder,
                'demo_message',
                TextType::class,
                [
                    'required' => false,
                ]
            );
        }
        $this->addWithDefaultCheckbox(
            $builder,
            'enabled',
            CheckboxType::class,
            [
                'required' => false,
            ]
        );

        $this->addWithDefaultCheckbox(
            $builder,
            'demo_string_collection',
            LiveCollectionType::class,
            [
                'entry_type' => TextType::class,
                'label' => 'Demo String Collection',
                'required' => false,
                'allow_add' => true,
                'allow_delete' => true,
                'by_reference' => false,
                'delete_empty' => true,
                'button_delete_options' => [
                    'attr' => [
                        'class' => 'btn-outline-danger',
                    ],
                ],
            ]
        );
    }
}
