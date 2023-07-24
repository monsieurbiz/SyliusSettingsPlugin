<?php

/*
 * This file is part of SyliusSettingsPlugin corporate website.
 *
 * (c) SyliusSettingsPlugin <sylius+syliussettingsplugin@monsieurbiz.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

declare(strict_types=1);

namespace MonsieurBiz\SyliusSettingsPlugin\Fixture\Factory;

use Sylius\Bundle\CoreBundle\Fixture\Factory\AbstractExampleFactory;
use Sylius\Component\Resource\Factory\FactoryInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class SettingsFixtureFactory extends AbstractExampleFactory
{
    private FactoryInterface $settingsFactory;

    private OptionsResolver $optionsResolver;

    public function __construct(
        FactoryInterface $settingsFactory,
    )
    {
        $this->settingsFactory = $settingsFactory;
        $this->optionsResolver = new OptionsResolver();
    }

    protected function configureOptions(OptionsResolver $resolver): void
    {
        // TODO: Implement configureOptions() method.
    }

    public function create(array $options = [])
    {
        // TODO: Implement create() method.
    }
}
