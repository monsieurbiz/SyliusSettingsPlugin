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
use MonsieurBiz\SyliusSettingsPlugin\Settings\CategorizedSettingsInterface;
use MonsieurBiz\SyliusSettingsPlugin\Settings\SettingsInterface;
use PHPUnit\Framework\TestCase;
use Psr\Log\AbstractLogger;
use Psr\Log\NullLogger;
use Stringable;
use Symfony\Component\Cache\Adapter\ArrayAdapter;
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
            new ArrayAdapter(),
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
            new ArrayAdapter(),
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
            new ArrayAdapter(),
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
            new ArrayAdapter(),
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
            new ArrayAdapter(),
            $logger,
        );

        $builder->build([$this->createSettings(ThrowingSearchSettingsType::class)]);

        self::assertCount(1, $logger->logs);
        self::assertSame('Unable to build settings search field index.', $logger->logs[0]['message']);
        self::assertSame('warning', $logger->logs[0]['level']);
        self::assertSame([
            'settings_alias' => 'app.blog',
            'form_class' => ThrowingSearchSettingsType::class,
            'throwable_class' => Error::class,
        ], $logger->logs[0]['context']);
        self::assertStringNotContainsString('sensitive-token', $logger->logs[0]['message']);
        self::assertStringNotContainsString('sensitive-token', json_encode($logger->logs[0]['context'], \JSON_THROW_ON_ERROR));
    }

    public function testItKeepsSettingsWithoutCategoryUsable(): void
    {
        $builder = new SettingsSearchIndexBuilder(
            Forms::createFormFactoryBuilder()
                ->addExtension(new CoreExtension())
                ->addType(new SearchSettingsType())
                ->getFormFactory(),
            $this->createTranslator(),
            new ArrayAdapter(),
            new NullLogger(),
        );

        $settings = $this->createMock(SettingsInterface::class);
        $settings->method('getAlias')->willReturn('app.custom');
        $settings->method('getVendorName')->willReturn('Acme');
        $settings->method('getPluginName')->willReturn('Custom settings');
        $settings->method('getDescription')->willReturn('Custom description');
        $settings->method('getFormClass')->willReturn(SearchSettingsType::class);

        $index = $builder->build([$settings]);

        self::assertArrayHasKey('app.custom', $index);
        self::assertSame([
            'app.custom',
            'Acme',
            'Custom settings',
            'Custom description',
        ], $index['app.custom']['metadata']);
        self::assertContains('title', $index['app.custom']['fields']);
    }

    public function testItCachesFieldTermsForSameAliasFormClassAndLocale(): void
    {
        CountingSearchSettingsType::$builds = 0;
        $builder = new SettingsSearchIndexBuilder(
            Forms::createFormFactoryBuilder()
                ->addExtension(new CoreExtension())
                ->addType(new CountingSearchSettingsType())
                ->getFormFactory(),
            $this->createTranslator(),
            new ArrayAdapter(),
            new NullLogger(),
        );

        $settings = $this->createSettings(CountingSearchSettingsType::class);

        $builder->build([$settings]);
        $builder->build([$settings]);

        self::assertSame(1, CountingSearchSettingsType::$builds);
    }

    public function testItUsesSeparateFieldCacheEntriesForDifferentLocales(): void
    {
        CountingSearchSettingsType::$builds = 0;
        $translator = new MutableTranslator('en');
        $builder = new SettingsSearchIndexBuilder(
            Forms::createFormFactoryBuilder()
                ->addExtension(new CoreExtension())
                ->addType(new CountingSearchSettingsType())
                ->getFormFactory(),
            $translator,
            new ArrayAdapter(),
            new NullLogger(),
        );

        $settings = $this->createSettings(CountingSearchSettingsType::class);

        $builder->build([$settings]);
        $translator->locale = 'fr';
        $builder->build([$settings]);

        self::assertSame(2, CountingSearchSettingsType::$builds);
    }

    public function testItUsesSeparateFieldCacheEntriesForDifferentAliasesAndFormClasses(): void
    {
        CountingSearchSettingsType::$builds = 0;
        AlternateCountingSearchSettingsType::$builds = 0;
        $builder = new SettingsSearchIndexBuilder(
            Forms::createFormFactoryBuilder()
                ->addExtension(new CoreExtension())
                ->addType(new CountingSearchSettingsType())
                ->addType(new AlternateCountingSearchSettingsType())
                ->getFormFactory(),
            $this->createTranslator(),
            new ArrayAdapter(),
            new NullLogger(),
        );

        $builder->build([
            $this->createSettings(CountingSearchSettingsType::class, 'app.first'),
            $this->createSettings(CountingSearchSettingsType::class, 'app.second'),
            $this->createSettings(AlternateCountingSearchSettingsType::class, 'app.first'),
        ]);

        self::assertSame(2, CountingSearchSettingsType::$builds);
        self::assertSame(1, AlternateCountingSearchSettingsType::$builds);
    }

    public function testItDoesNotCacheMetadataTerms(): void
    {
        $builder = new SettingsSearchIndexBuilder(
            Forms::createFormFactoryBuilder()
                ->addExtension(new CoreExtension())
                ->addType(new CountingSearchSettingsType())
                ->getFormFactory(),
            $this->createTranslator(),
            new ArrayAdapter(),
            new NullLogger(),
        );

        $settings = $this->createMock(CategorizedSettingsInterface::class);
        $settings->method('getAlias')->willReturn('app.blog');
        $settings->method('getVendorName')->willReturn('Acme');
        $settings->method('getCategory')->willReturn('Content');
        $settings->method('getDescription')->willReturn('Description');
        $settings->method('getFormClass')->willReturn(CountingSearchSettingsType::class);
        $pluginName = 'First name';
        $settings->method('getPluginName')->willReturnCallback(static function () use (&$pluginName): string {
            return $pluginName;
        });

        $index = $builder->build([$settings]);
        self::assertContains('First name', $index['app.blog']['metadata']);

        $pluginName = 'Second name';
        $index = $builder->build([$settings]);

        self::assertContains('Second name', $index['app.blog']['metadata']);
        self::assertNotContains('First name', $index['app.blog']['metadata']);
    }

    /**
     * @param class-string $formClass
     */
    private function createSettings(string $formClass = SearchSettingsType::class, string $alias = 'app.blog'): SettingsInterface
    {
        $settings = $this->createMock(CategorizedSettingsInterface::class);
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
        return new MutableTranslator('en');
    }
}

final class MutableTranslator implements TranslatorInterface
{
    public function __construct(public string $locale)
    {
    }

    public function trans(string $id, array $parameters = [], ?string $domain = null, ?string $locale = null): string
    {
        return strtr($id, $parameters);
    }

    public function getLocale(): string
    {
        return $this->locale;
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

final class CountingSearchSettingsType extends AbstractSettingsType
{
    public static int $builds = 0;

    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        ++self::$builds;
        $builder->add('counted_field', TextType::class, ['label' => 'Counted field']);
    }
}

final class AlternateCountingSearchSettingsType extends AbstractSettingsType
{
    public static int $builds = 0;

    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        ++self::$builds;
        $builder->add('alternate_counted_field', TextType::class, ['label' => 'Alternate counted field']);
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
