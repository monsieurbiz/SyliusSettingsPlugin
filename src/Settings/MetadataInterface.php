<?php

declare(strict_types=1);

namespace MonsieurBiz\SyliusSettingsPlugin\Settings;

interface MetadataInterface
{
    public static function fromAliasAndConfiguration(string $alias, array $parameters): self;
    public function getAlias(): string;
    public function getApplicationName(bool $aliased = false): string;
    public function getName(bool $aliased = false): string;
    public function getParameter(string $name);
    public function hasParameter(string $name): bool;
    public function getParameters(): array;
    public function getClass(string $name): string;
    public function hasClass(string $name): bool;
    public function getServiceId(string $serviceName): string;
}
