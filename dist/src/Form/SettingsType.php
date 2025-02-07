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

use MonsieurBiz\SyliusSettingsPlugin\Attribute\AsSetting;
use MonsieurBiz\SyliusSettingsPlugin\Form\AbstractSettingsType;
use MonsieurBiz\SyliusSettingsPlugin\Form\SettingsTypeInterface;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Validator\Constraints as Assert;

#[AsSetting(
    alias: 'app.default',
    vendorName: 'Monsieur Biz',
    pluginName: 'Current App',
    description: 'Platform\'s settings',
    icon: 'bi:bullseye',
    useLocales: true,
    defaultValues: [
        'demo_message' => 'My amazing message',
        'demo_title' => 'My amazing title',
        'enabled' => true,
    ],
)]
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
    }
}
