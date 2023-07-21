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

namespace MonsieurBiz\SyliusSettingsPlugin\Settings;

use InvalidArgumentException;

final class Metadata implements MetadataInterface
{
    private string $name;

    private string $applicationName;

    private array $parameters;

    /**
     * Metadata constructor.
     */
    private function __construct(string $name, string $applicationName, array $parameters)
    {
        $this->name = $name;
        $this->applicationName = $applicationName;
        $this->parameters = $parameters;
    }

    public static function fromAliasAndConfiguration(string $alias, array $parameters): self
    {
        [$applicationName, $name] = self::parseAlias($alias);

        return new self($name, $applicationName, $parameters);
    }

    /**
     * @inheritdoc
     */
    public function getAlias(): string
    {
        return $this->getApplicationName(true) . '.' . $this->getName(true);
    }

    /**
     * @inheritdoc
     *
     * @SuppressWarnings(PHPMD.BooleanArgumentFlag)
     */
    public function getApplicationName(bool $aliased = false): string
    {
        return $aliased ? $this->alias($this->applicationName) : $this->applicationName;
    }

    /**
     * @inheritdoc
     *
     * @SuppressWarnings(PHPMD.BooleanArgumentFlag)
     */
    public function getName(bool $aliased = false): string
    {
        return $aliased ? $this->alias($this->name) : $this->name;
    }

    /**
     * @inheritdoc
     */
    public function getParameter(string $name)
    {
        if (!$this->hasParameter($name)) {
            throw new InvalidArgumentException(sprintf('Parameter "%s" is not configured for resource "%s".', $name, $this->getAlias()));
        }

        return $this->parameters[$name];
    }

    /**
     * @inheritdoc
     */
    public function hasParameter(string $name): bool
    {
        return \array_key_exists($name, $this->parameters);
    }

    /**
     * @inheritdoc
     */
    public function getParameters(): array
    {
        return $this->parameters;
    }

    public function getDefaultValues(): array
    {
        return $this->parameters['default_values'];
    }

    public function getDefaultValuesForChannels(): array
    {
        return $this->parameters['default_values_for_channels'];
    }

    /**
     * @inheritdoc
     */
    public function getClass(string $name): string
    {
        if (!$this->hasClass($name)) {
            throw new InvalidArgumentException(sprintf('Class "%s" is not configured for resource "%s".', $name, $this->getAlias()));
        }

        return $this->parameters['classes'][$name];
    }

    /**
     * @inheritdoc
     */
    public function hasClass(string $name): bool
    {
        return isset($this->parameters['classes'][$name]);
    }

    /**
     * @inheritdoc
     */
    public function getServiceId(string $serviceName): string
    {
        return sprintf('%s.%s.%s', $this->applicationName, $serviceName, $this->alias($this->name));
    }

    private static function parseAlias(string $alias): array
    {
        if (false === strpos($alias, '.')) {
            throw new InvalidArgumentException(sprintf('Invalid alias "%s" supplied, it should conform to the following format "<applicationName>.<name>".', $alias));
        }

        return explode('.', $alias);
    }

    private static function alias(string $string): string
    {
        return strtolower(preg_replace('`([A-Z])`', '_\1', lcfirst($string)) ?? '');
    }

    /**
     * @inheritdoc
     */
    public function useLocales(): bool
    {
        if (!$this->hasParameter('use_locales')) {
            return true;
        }

        return (bool) $this->getParameter('use_locales');
    }
}
