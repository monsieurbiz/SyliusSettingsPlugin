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

namespace MonsieurBiz\SyliusSettingsPlugin\Tests\Unit\Twig;

use PHPUnit\Framework\TestCase;
use Symfony\Bridge\Twig\Extension\TranslationExtension;
use Symfony\Contracts\Translation\TranslatorInterface;
use Twig\Environment;
use Twig\Loader\FilesystemLoader;
use Twig\TwigFunction;

final class SettingsCardsTemplateTest extends TestCase
{
    public function testItAddsDisplayedCategoryLabelsToSearchMetadata(): void
    {
        $twig = new Environment(new FilesystemLoader(\dirname(__DIR__, 3) . '/src/Resources/views'));
        $twig->addExtension(new TranslationExtension($this->createTranslator()));
        $twig->addFunction(new TwigFunction('ux_icon', static fn (?string $icon): string => ''));
        $twig->addFunction(new TwigFunction('path', static fn (string $route, array $parameters = []): string => '#'));

        $html = $twig->render('admin/settings/index/content/cards.html.twig', [
            'hookable_metadata' => [
                'context' => [
                    'settings' => [
                        [
                            'alias' => 'app.explicit',
                            'category' => 'app.category.content',
                            'icon' => null,
                            'pluginName' => 'Explicit settings',
                            'vendorName' => 'Acme',
                            'description' => 'Explicit description',
                        ],
                        [
                            'alias' => 'app.fallback',
                            'category' => null,
                            'icon' => null,
                            'pluginName' => 'Fallback settings',
                            'vendorName' => 'Acme',
                            'description' => 'Fallback description',
                        ],
                    ],
                    'settings_search_index' => [
                        'app.explicit' => ['metadata' => ['explicit metadata'], 'fields' => []],
                        'app.fallback' => ['metadata' => ['fallback metadata'], 'fields' => []],
                    ],
                ],
            ],
        ]);

        self::assertStringContainsString('data-search-metadata="explicit&#x20;metadata&#x20;content&#x20;label"', $html);
        self::assertStringContainsString('data-search-metadata="fallback&#x20;metadata&#x20;other"', $html);
        self::assertStringContainsString('data-search-text="explicit&#x20;metadata&#x20;content&#x20;label"', $html);
        self::assertStringContainsString('data-search-text="fallback&#x20;metadata&#x20;other"', $html);
    }

    private function createTranslator(): TranslatorInterface
    {
        return new class() implements TranslatorInterface {
            public function trans(string $id, array $parameters = [], ?string $domain = null, ?string $locale = null): string
            {
                return strtr($id, [
                    'app.category.content' => 'Content label',
                    'monsieurbiz.settings.ui.category.other' => 'Other',
                ] + $parameters);
            }

            public function getLocale(): string
            {
                return 'en';
            }
        };
    }
}
