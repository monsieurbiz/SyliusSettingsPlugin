<?php

declare(strict_types=1);

namespace MonsieurBiz\SyliusSettingsPlugin\Settings;

final class Metadata implements MetadataInterface
{
    /**
     * @var string
     */
    private string $name;

    /**
     * @var string
     */
    private string $applicationName;

    /**
     * @var array
     */
    private array $parameters;

    /**
     * Metadata constructor.
     *
     * @param string $name
     * @param string $applicationName
     * @param array $parameters
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
     * {@inheritdoc}
     */
    public function getAlias(): string
    {
        return $this->applicationName . '.' . $this->alias($this->name);
    }

    /**
     * {@inheritdoc}
     */
    public function getApplicationName(): string
    {
        return $this->applicationName;
    }

    /**
     * {@inheritdoc}
     */
    public function getName(): string
    {
        return $this->name;
    }

    /**
     * {@inheritdoc}
     */
    public function getParameter(string $name)
    {
        if (!$this->hasParameter($name)) {
            throw new \InvalidArgumentException(sprintf('Parameter "%s" is not configured for resource "%s".', $name, $this->getAlias()));
        }

        return $this->parameters[$name];
    }

    /**
     * {@inheritdoc}
     */
    public function hasParameter(string $name): bool
    {
        return array_key_exists($name, $this->parameters);
    }

    /**
     * {@inheritdoc}
     */
    public function getParameters(): array
    {
        return $this->parameters;
    }

    /**
     * {@inheritdoc}
     */
    public function getServiceId(string $serviceName): string
    {
        return sprintf('%s.%s.%s', $this->applicationName, $serviceName, $this->alias($this->name));
    }

    private static function parseAlias(string $alias): array
    {
        if (false === strpos($alias, '.')) {
            throw new \InvalidArgumentException(sprintf('Invalid alias "%s" supplied, it should conform to the following format "<applicationName>.<name>".', $alias));
        }

        return explode('.', $alias);
    }

    private static function alias(string $string): string
    {
        return strtolower(preg_replace('`([A-Z])`', '_\1', lcfirst($string)));
    }
}
