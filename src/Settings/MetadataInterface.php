<?php

declare(strict_types=1);

namespace MonsieurBiz\SyliusSettingsPlugin\Settings;

interface MetadataInterface
{
    public static function fromAliasAndConfiguration(string $alias, array $parameters): self;
    public function getAlias(): string;
    public function getApplicationName(): string;
    public function getName(): string;
    public function getParameter(string $name);
    public function hasParameter(string $name): bool;
    public function getParameters(): array;
    public function getServiceId(string $serviceName): string;
}
