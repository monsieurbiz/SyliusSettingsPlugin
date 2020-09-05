<?php

declare(strict_types=1);

namespace App\Form;

use MonsieurBiz\SyliusSettingsPlugin\Form\AbstractSettingsType;
use MonsieurBiz\SyliusSettingsPlugin\Form\SettingsTypeInterface;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Validator\Constraints as Assert;

class SettingsType extends AbstractSettingsType implements SettingsTypeInterface
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        if ($this->isDefaultForm($builder)) {
            $this->addWithDefaultCheckbox(
                $builder, 'demo_message', TextType::class, [
                'required' => true,
                'constraints' => [
                    new Assert\NotBlank(),
                ],
            ]);
        } else {
            $this->addWithDefaultCheckbox(
                $builder, 'demo_message', TextType::class, [
                'required' => false,
            ]);
        }
        $this->addWithDefaultCheckbox(
            $builder, 'enabled', CheckboxType::class, [
            'required' => false,
        ]);
    }
}
