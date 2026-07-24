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

namespace MonsieurBiz\SyliusSettingsPlugin\Search;

use MonsieurBiz\SyliusSettingsPlugin\Settings\CategorizedSettingsInterface;
use MonsieurBiz\SyliusSettingsPlugin\Settings\Settings;
use MonsieurBiz\SyliusSettingsPlugin\Settings\SettingsInterface;
use Psr\Cache\CacheItemPoolInterface;
use Psr\Log\LoggerInterface;
use Symfony\Component\Form\FormFactoryInterface;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\Form\FormView;
use Symfony\Component\OptionsResolver\Exception\UndefinedOptionsException;
use Symfony\Contracts\Translation\TranslatorInterface;
use Throwable;

final class SettingsSearchIndexBuilder
{
    public function __construct(
        private FormFactoryInterface $formFactory,
        private TranslatorInterface $translator,
        private CacheItemPoolInterface $settingsSearchCache,
        private ?LoggerInterface $logger = null,
    ) {
    }

    /**
     * @param iterable<SettingsInterface> $settingsCollection
     *
     * @return array<string, array{metadata: list<string>, fields: list<string>}>
     */
    public function build(iterable $settingsCollection): array
    {
        $index = [];

        foreach ($settingsCollection as $settings) {
            $fields = $this->extractFieldTerms($settings);

            $index[$settings->getAlias()] = [
                'metadata' => $this->normalizeTerms($this->extractMetadataTerms($settings)),
                'fields' => $fields,
            ];
        }

        return $index;
    }

    /**
     * @return list<string|null>
     */
    private function extractMetadataTerms(SettingsInterface $settings): array
    {
        $category = $settings instanceof CategorizedSettingsInterface ? $settings->getCategory() : null;

        return [
            $settings->getAlias(),
            $settings->getVendorName(),
            $settings->getPluginName(),
            $category,
            $settings->getDescription(),
            $this->translate($settings->getPluginName()),
            $this->translate($category),
            $this->translate($settings->getDescription()),
        ];
    }

    /**
     * @return list<string>
     */
    private function extractFieldTerms(SettingsInterface $settings): array
    {
        $formClass = null;

        try {
            $formClass = $settings->getFormClass();
            $cacheKey = $this->getFieldTermsCacheKey($settings, $formClass);
            $cacheItem = $this->settingsSearchCache->getItem($cacheKey);
            if ($cacheItem->isHit()) {
                $fields = $cacheItem->get();

                return \is_array($fields) ? $fields : [];
            }

            $form = $this->createIntrospectionForm($settings, $formClass);

            $fields = $this->normalizeTerms($this->extractFormViewTerms($form->createView()));

            $cacheItem->set($fields);
            $this->settingsSearchCache->save($cacheItem);

            return $fields;
        } catch (Throwable $throwable) {
            $this->logger?->warning('Unable to build settings search field index.', [
                'settings_alias' => $settings->getAlias(),
                'form_class' => $formClass,
                'throwable_class' => $throwable::class,
            ]);

            return [];
        }
    }

    /**
     * @param class-string $formClass
     */
    private function getFieldTermsCacheKey(SettingsInterface $settings, string $formClass): string
    {
        return 'settings_search_fields_' . hash('sha256', implode('|', [
            $settings->getAlias(),
            $formClass,
            $this->translator->getLocale(),
        ]));
    }

    /**
     * @param class-string $formClass
     */
    private function createIntrospectionForm(SettingsInterface $settings, string $formClass): FormInterface
    {
        $options = [
            'settings' => $settings,
            'channel' => null,
            'show_default_checkboxes' => false,
            'csrf_protection' => false,
        ];

        try {
            return $this->formFactory->create($formClass, [], $options);
            // @phpstan-ignore-next-line FormFactoryInterface does not expose configured type extensions/options.
        } catch (UndefinedOptionsException) {
            unset($options['csrf_protection']);

            return $this->formFactory->create($formClass, [], $options);
        }
    }

    /**
     * @return list<string>
     */
    private function extractFormViewTerms(FormView $view, string $parentPath = ''): array
    {
        $terms = [];

        foreach ($view->children as $child) {
            $name = (string) $child->vars['name'];
            $path = '' === $parentPath ? $name : $parentPath . '.' . $name;

            if (!$this->shouldIndex($child)) {
                $terms = [...$terms, ...$this->extractFormViewTerms($child, $path)];

                continue;
            }

            $terms[] = $name;
            $terms[] = $path;
            $terms[] = $this->getLabel($child, $name);
            $terms[] = $this->getTranslatedFormVar($child, 'help', 'help_translation_parameters', 'help_translation_domain');
            $terms[] = $this->getTranslatedPlaceholder($child);
            $terms = [...$terms, ...$this->extractFormViewTerms($child, $path)];
        }

        return $terms;
    }

    private function shouldIndex(FormView $view): bool
    {
        $name = (string) $view->vars['name'];
        if ('_token' === $name || str_contains($name, '___' . Settings::DEFAULT_KEY)) {
            return false;
        }

        $blockPrefixes = $view->vars['block_prefixes'] ?? [];
        if (!\is_array($blockPrefixes)) {
            return true;
        }

        return [] === array_intersect($blockPrefixes, ['button', 'submit', 'reset', 'hidden', 'csrf_token']);
    }

    private function getLabel(FormView $view, string $fallbackName): string
    {
        if (false === $view->vars['label']) {
            return '';
        }

        $label = $view->vars['label'] ?? $this->humanize($fallbackName);

        return $this->translate(
            \is_string($label) ? $label : (string) $label,
            $view->vars['label_translation_parameters'] ?? [],
            $view->vars['translation_domain'] ?? null,
        );
    }

    private function getTranslatedPlaceholder(FormView $view): string
    {
        $placeholder = $view->vars['attr']['placeholder'] ?? $view->vars['placeholder'] ?? null;
        if (null === $placeholder || false === $placeholder) {
            return '';
        }

        return $this->translate(
            \is_string($placeholder) ? $placeholder : (string) $placeholder,
            $view->vars['attr_translation_parameters'] ?? [],
            $view->vars['translation_domain'] ?? null,
        );
    }

    private function getTranslatedFormVar(FormView $view, string $valueKey, string $parametersKey, string $domainKey): string
    {
        $value = $view->vars[$valueKey] ?? null;
        if (null === $value || false === $value) {
            return '';
        }

        return $this->translate(
            \is_string($value) ? $value : (string) $value,
            $view->vars[$parametersKey] ?? [],
            $view->vars[$domainKey] ?? $view->vars['translation_domain'] ?? null,
        );
    }

    /**
     * @param array<string, mixed> $parameters
     */
    private function translate(?string $message, array $parameters = [], string|false|null $domain = null): string
    {
        if (null === $message || '' === $message) {
            return '';
        }

        $translated = false === $domain ? $message : $this->translator->trans($message, $parameters, $domain);

        return strip_tags($translated);
    }

    /**
     * @param list<string|null> $terms
     *
     * @return list<string>
     */
    private function normalizeTerms(array $terms): array
    {
        $normalizedTerms = [];
        foreach ($terms as $term) {
            if (null === $term) {
                continue;
            }

            $term = trim((string) $term);
            if ('' !== $term) {
                $normalizedTerms[] = $term;
            }
        }

        return array_values(array_unique($normalizedTerms));
    }

    private function humanize(string $text): string
    {
        return ucfirst(strtolower(trim((string) preg_replace(['/([A-Z])/', '/[_\s]+/'], ['_$1', ' '], $text))));
    }
}
