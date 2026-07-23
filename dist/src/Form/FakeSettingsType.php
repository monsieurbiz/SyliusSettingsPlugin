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
use MonsieurBiz\SyliusSettingsPlugin\Settings\SettingsInterface;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\UrlType;
use Symfony\Component\Form\FormBuilderInterface;

final class FakeSettingsType extends AbstractSettingsType implements SettingsTypeInterface
{
    /**
     * @var array<string, array{field: string, type: class-string, options: array<string, mixed>}>
     */
    private const FIELDS_BY_ALIAS = [
        'app.fake_blog' => [
            'field' => 'blog_headline',
            'type' => TextType::class,
            'options' => [
                'label' => 'Blog headline',
                'attr' => ['placeholder' => 'Enter the blog landing headline'],
                'help' => 'Shown above the latest blog articles.',
            ],
        ],
        'app.fake_checkout' => [
            'field' => 'checkout_notice',
            'type' => TextareaType::class,
            'options' => [
                'label' => 'Checkout notice',
                'attr' => ['placeholder' => 'Write a short checkout notice'],
                'help' => 'Displayed before customers confirm their cart.',
            ],
        ],
        'app.fake_catalog' => [
            'field' => 'catalog_page_size',
            'type' => IntegerType::class,
            'options' => [
                'label' => 'Catalog page size',
                'attr' => ['placeholder' => 'Products per catalog page'],
                'help' => 'Controls how many products appear in listings.',
            ],
        ],
        'app.fake_marketing' => [
            'field' => 'marketing_sender_email',
            'type' => EmailType::class,
            'options' => [
                'label' => 'Marketing sender email',
                'attr' => ['placeholder' => 'newsletter@example.com'],
                'help' => 'Used as the sender address for campaigns.',
            ],
        ],
        'app.fake_shipping' => [
            'field' => 'shipping_tracking_url',
            'type' => UrlType::class,
            'options' => [
                'label' => 'Shipping tracking URL',
                'attr' => ['placeholder' => 'https://carrier.example/track/{number}'],
                'help' => 'Template used to link shipment tracking numbers.',
            ],
        ],
        'app.fake_payment' => [
            'field' => 'payment_retry_limit',
            'type' => IntegerType::class,
            'options' => [
                'label' => 'Payment retry limit',
                'attr' => ['placeholder' => 'Maximum payment retries'],
                'help' => 'Limits how many times a failed payment is retried.',
            ],
        ],
        'app.fake_reviews' => [
            'field' => 'reviews_moderation_note',
            'type' => TextareaType::class,
            'options' => [
                'label' => 'Reviews moderation note',
                'attr' => ['placeholder' => 'Explain the review moderation rule'],
                'help' => 'Internal note for the review moderation team.',
            ],
        ],
        'app.fake_loyalty' => [
            'field' => 'loyalty_points_ratio',
            'type' => ChoiceType::class,
            'options' => [
                'label' => 'Loyalty points ratio',
                'placeholder' => 'Choose a loyalty earning ratio',
                'help' => 'Defines how quickly customers earn reward points.',
                'choices' => [
                    'One point per euro' => '1_per_euro',
                    'Two points per euro' => '2_per_euro',
                    'Five points per euro' => '5_per_euro',
                ],
            ],
        ],
        'app.fake_seo' => [
            'field' => 'seo_meta_description',
            'type' => TextareaType::class,
            'options' => [
                'label' => 'SEO meta description',
                'attr' => ['placeholder' => 'Describe the storefront for search engines'],
                'help' => 'Fallback description used by search result snippets.',
            ],
        ],
        'app.fake_analytics' => [
            'field' => 'analytics_tracking_id',
            'type' => TextType::class,
            'options' => [
                'label' => 'Analytics tracking ID',
                'attr' => ['placeholder' => 'G-XXXXXXXXXX'],
                'help' => 'Identifier used by the analytics integration.',
            ],
        ],
    ];

    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $settings = $options['settings'];
        if (!$settings instanceof SettingsInterface) {
            return;
        }

        $fieldConfig = self::FIELDS_BY_ALIAS[$settings->getAlias()] ?? null;
        if (null === $fieldConfig) {
            return;
        }

        $this->addWithDefaultCheckbox(
            $builder,
            $fieldConfig['field'],
            $fieldConfig['type'],
            $fieldConfig['options'] + [
                'required' => false,
            ],
        );
    }
}
