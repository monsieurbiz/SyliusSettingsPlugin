<?php

declare(strict_types=1);

namespace MonsieurBiz\SyliusSettingsPlugin\Settings;

use MonsieurBiz\SyliusSettingsPlugin\Exception\SettingsException;
use MonsieurBiz\SyliusSettingsPlugin\Form\AbstractSettingsType;

class Settings implements SettingsInterface
{

    /**
     * @var Metadata
     */
    private Metadata $metadata;

    /**
     * Settings constructor.
     *
     * @param Metadata $metadata
     */
    public function __construct(Metadata $metadata)
    {
        $this->metadata = $metadata;
    }

    public function getAlias(): string
    {
        return $this->metadata->getAlias();
    }

    public function getVendorName(): string
    {
        return $this->metadata->getParameter('vendor_name');
    }

    public function getVendorUrl(): ?string
    {
        return $this->metadata->getParameter('vendor_url');
    }

    public function getPluginName(): string
    {
        return $this->metadata->getParameter('plugin_name');
    }

    public function getDescription(): string
    {
        return $this->metadata->getParameter('description');
    }

    public function getIcon(): string
    {
        return $this->metadata->getParameter('icon');
    }

    /**
     * @return string
     * @throws SettingsException
     */
    public function getFormClass(): string
    {
        $className = $this->metadata->getClass('form');
        if (!in_array(AbstractSettingsType::class, class_parents($className))) {
            throw new SettingsException(sprintf('Class %s should extend %s', $className, AbstractSettingsType::class));
        }
        return $className;
    }
}
