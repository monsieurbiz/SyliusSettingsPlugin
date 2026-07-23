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

namespace MonsieurBiz\SyliusSettingsPlugin\Tests\Unit\Search;

require_once __DIR__ . '/../../../dist/src/Form/FakeSettingsType.php';

use Error;
use MonsieurBiz\SyliusSettingsPlugin\Form\AbstractSettingsType;
use MonsieurBiz\SyliusSettingsPlugin\Search\SettingsSearchIndexBuilder;
use MonsieurBiz\SyliusSettingsPlugin\Settings\SettingsInterface;
use PHPUnit\Framework\TestCase;
use Psr\Log\AbstractLogger;
use Psr\Log\NullLogger;
use Stringable;
use Symfony\Component\Form\AbstractTypeExtension;
use Symfony\Component\Form\Extension\Core\CoreExtension;
use Symfony\Component\Form\Extension\Core\Type\FormType;
use Symfony\Component\Form\Extension\Core\Type\HiddenType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\Forms;
use Symfony\Contracts\Translation\TranslatorInterface;

final class SettingsSearchIndexBuilderTest extends TestCase
{
    public function testItSeparatesMetadataAndFormFieldTerms(): void
    {
        $builder = new SettingsSearchIndexBuilder(
            Forms::createFormFactoryBuilder()
                ->addExtension(new CoreExtension())
                ->addType(new SearchSettingsType())
                ->addTypeExtension(new SearchSettingsTypeExtension())
                ->getFormFactory(),
            $this->createTranslator(),
            new NullLogger(),
        );

        $index = $builder->build([$this->createSettings()]);

        self::assertArrayHasKey('app.blog', $index);
        self::assertSame([
            'app.blog',
            'Acme',
            'Blog settings',
            'Content',
            'Configure the blog',
        ], $index['app.blog']['metadata']);

        self::assertContains('title', $index['app.blog']['fields']);
        self::assertContains('Title label', $index['app.blog']['fields']);
        self::assertContains('Type a title', $index['app.blog']['fields']);
        self::assertContains('Shown on the storefront', $index['app.blog']['fields']);
        self::assertContains('nested.child_name', $index['app.blog']['fields']);
        self::assertContains('Child name', $index['app.blog']['fields']);
        self::assertContains('Extension field', $index['app.blog']['fields']);
        self::assertContains('Without label', $index['app.blog']['fields']);
    }

    public function testItDoesNotIndexSavedDataOrDefaultValues(): void
    {
        $builder = new SettingsSearchIndexBuilder(
            Forms::createFormFactoryBuilder()->addExtension(new CoreExtension())->getFormFactory(),
            $this->createTranslator(),
            new NullLogger(),
        );

        /** @var \PHPUnit\Framework\MockObject\MockObject&SettingsInterface $settings */
        $settings = $this->createSettings();
        $settings
            ->expects(self::never())
            ->method('getDefaultValues')
        ;
        $settings
            ->expects(self::never())
            ->method('getSettingsValuesByChannelAndLocale')
        ;

        $index = $builder->build([$settings]);
        $fieldTerms = implode(' ', $index['app.blog']['fields']);

        self::assertStringNotContainsString('saved database value', $fieldTerms);
        self::assertStringNotContainsString('default metadata value', $fieldTerms);
        self::assertStringNotContainsString('technical_secret', $fieldTerms);
    }

    public function testItIndexesDistinctFakeSettingsFieldsByAlias(): void
    {
        $builder = new SettingsSearchIndexBuilder(
            Forms::createFormFactoryBuilder()
                ->addExtension(new CoreExtension())
                ->addType(new \App\Form\FakeSettingsType())
                ->getFormFactory(),
            $this->createTranslator(),
            new NullLogger(),
        );

        $index = $builder->build([
            $this->createSettings(\App\Form\FakeSettingsType::class, 'app.fake_blog'),
            $this->createSettings(\App\Form\FakeSettingsType::class, 'app.fake_payment'),
        ]);

        self::assertContains('blog_headline', $index['app.fake_blog']['fields']);
        self::assertContains('Blog headline', $index['app.fake_blog']['fields']);
        self::assertContains('Enter the blog landing headline', $index['app.fake_blog']['fields']);
        self::assertContains('Shown above the latest blog articles.', $index['app.fake_blog']['fields']);
        self::assertNotContains('payment_retry_limit', $index['app.fake_blog']['fields']);

        self::assertContains('payment_retry_limit', $index['app.fake_payment']['fields']);
        self::assertContains('Payment retry limit', $index['app.fake_payment']['fields']);
        self::assertContains('Maximum payment retries', $index['app.fake_payment']['fields']);
        self::assertContains('Limits how many times a failed payment is retried.', $index['app.fake_payment']['fields']);
        self::assertNotContains('blog_headline', $index['app.fake_payment']['fields']);
    }

    public function testItIgnoresThrowableRaisedDuringFormIntrospection(): void
    {
        $builder = new SettingsSearchIndexBuilder(
            Forms::createFormFactoryBuilder()
                ->addExtension(new CoreExtension())
                ->addType(new ThrowingSearchSettingsType())
                ->getFormFactory(),
            $this->createTranslator(),
            new NullLogger(),
        );

        $index = $builder->build([$this->createSettings(ThrowingSearchSettingsType::class)]);

        self::assertSame([], $index['app.blog']['fields']);
    }

    public function testItDoesNotLogThrowableMessage(): void
    {
        $logger = new InMemoryLogger();
        $builder = new SettingsSearchIndexBuilder(
            Forms::createFormFactoryBuilder()
                ->addExtension(new CoreExtension())
                ->addType(new ThrowingSearchSettingsType())
                ->getFormFactory(),
            $this->createTranslator(),
            $logger,
        );

        $builder->build([$this->createSettings(ThrowingSearchSettingsType::class)]);

        self::assertCount(1, $logger->logs);
        self::assertSame('Unable to build settings search field index.', $logger->logs[0]['message']);
        self::assertSame([
            'settings_alias' => 'app.blog',
            'form_class' => ThrowingSearchSettingsType::class,
            'throwable_class' => Error::class,
        ], $logger->logs[0]['context']);
        self::assertStringNotContainsString('sensitive-token', $logger->logs[0]['message']);
        self::assertStringNotContainsString('sensitive-token', json_encode($logger->logs[0]['context'], \JSON_THROW_ON_ERROR));
    }

    /**
     * @param class-string $formClass
     */
    private function createSettings(string $formClass = SearchSettingsType::class, string $alias = 'app.blog'): SettingsInterface
    {
        $settings = $this->createMock(SettingsInterface::class);
        $settings->method('getAlias')->willReturn($alias);
        $settings->method('getVendorName')->willReturn('Acme');
        $settings->method('getPluginName')->willReturn('Blog settings');
        $settings->method('getCategory')->willReturn('Content');
        $settings->method('getDescription')->willReturn('Configure the blog');
        $settings->method('getFormClass')->willReturn($formClass);

        return $settings;
    }

    private function createTranslator(): TranslatorInterface
    {
        return new class() implements TranslatorInterface {
            public function trans(string $id, array $parameters = [], ?string $domain = null, ?string $locale = null): string
            {
                return strtr($id, $parameters);
            }

            public function getLocale(): string
            {
                return 'en';
            }
        };
    }
}

final class SearchSettingsType extends AbstractSettingsType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('title', TextType::class, [
                'label' => 'Title label',
                'attr' => [
                    'placeholder' => 'Type a title',
                ],
                'help' => 'Shown on the storefront',
                'data' => 'saved database value',
            ])
            ->add('without_label', TextType::class)
            ->add('technical_secret', HiddenType::class, [
                'data' => 'technical_secret',
            ])
            ->add('title___default', TextType::class)
            ->add('nested', FormType::class, [
                'label' => 'Nested group',
            ])
        ;

        $builder->get('nested')->add('child_name', TextType::class, [
            'label' => 'Child name',
        ]);
    }
}

final class SearchSettingsTypeExtension extends AbstractTypeExtension
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder->add('extension_field', TextType::class, [
            'label' => 'Extension field',
        ]);
    }

    /**
     * @return iterable<class-string>
     */
    public static function getExtendedTypes(): iterable
    {
        return [SearchSettingsType::class];
    }
}

final class ThrowingSearchSettingsType extends AbstractSettingsType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        throw new Error('sensitive-token must not be logged');
    }
}

final class InMemoryLogger extends AbstractLogger
{
    /**
     * @var list<array{level: mixed, message: string, context: array<string, mixed>}>
     */
    public array $logs = [];

    /**
     * @param array<string, mixed> $context
     * @param mixed $level
     */
    public function log($level, string|Stringable $message, array $context = []): void
    {
        $this->logs[] = [
            'level' => $level,
            'message' => (string) $message,
            'context' => $context,
        ];
    }
}
