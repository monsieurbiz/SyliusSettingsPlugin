<?php

declare(strict_types=1);

namespace MonsieurBiz\SyliusSettingsPlugin\DependencyInjection;

use MonsieurBiz\SyliusSettingsPlugin\Settings\Metadata;
use MonsieurBiz\SyliusSettingsPlugin\Settings\Settings;
use MonsieurBiz\SyliusSettingsPlugin\Settings\SettingsInterface;
use Sylius\Component\Product\Generator\SlugGenerator;
use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Definition;
use Symfony\Component\DependencyInjection\Exception\InvalidArgumentException;
use Symfony\Component\DependencyInjection\Reference;

final class InstantiateSettingsPass implements CompilerPassInterface
{
    /**
     * @inheritDoc
     */
    public function process(ContainerBuilder $container)
    {
        try {
            $plugins = $container->getParameter('monsieurbiz.settings.config.plugins');
            $registry = $container->findDefinition('monsieurbiz.settings_registry');
        } catch (InvalidArgumentException $exception) {
            return;
        }

        foreach ($plugins as $alias => $configuration) {
            $registry->addMethodCall('addFromAliasAndConfiguration', [$alias, $configuration]);
            $metadata = Metadata::fromAliasAndConfiguration($alias, $configuration);

            $id = $metadata->getServiceId('settings');

            // TODO Add dynamism to the class by using "classes" in parameters
            $container->setDefinition($id, new Definition(Settings::class, [
                $this->getMetadataDefinition($metadata),
            ]));

            $container->addAliases([
                SettingsInterface::class . ' $' . $metadata->getName() . 'Settings' => $id,
                Settings::class . ' $' . $metadata->getName() . 'Settings' => $id,
            ]);
        }
    }

    /**
     * @param Metadata $metadata
     *
     * @return Definition
     */
    private function getMetadataDefinition(Metadata $metadata): Definition
    {
        $metadataDefinition = new Definition(Metadata::class);
        $metadataDefinition
            ->setFactory([new Reference('monsieurbiz.settings_registry'), 'get'])
            ->setArguments([$metadata->getAlias()])
        ;
        return $metadataDefinition;
    }
}
