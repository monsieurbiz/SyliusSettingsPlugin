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

use MonsieurBiz\SyliusSettingsPlugin\Form\AbstractSettingsExtension;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Validator\Constraints as Assert;

class SettingsExtensionType extends AbstractSettingsExtension
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        if ($this->isDefaultForm($builder)) {
            $this->addWithDefaultCheckbox(
                $builder,
                'demo_title',
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
                'demo_title',
                TextType::class,
                [
                    'required' => false,
                ]
            );
        }
    }

    public static function getExtendedTypes(): array
    {
        return [
            SettingsType::class,
        ];
    }
}
