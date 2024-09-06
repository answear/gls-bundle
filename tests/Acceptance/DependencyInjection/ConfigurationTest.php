<?php

declare(strict_types=1);

namespace Answear\GlsBundle\Tests\Acceptance\DependencyInjection;

use Answear\GlsBundle\ConfigProvider;
use Answear\GlsBundle\DependencyInjection\AnswearGlsExtension;
use Answear\GlsBundle\DependencyInjection\Configuration;
use Matthias\SymfonyConfigTest\PhpUnit\ConfigurationTestCaseTrait;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Exception\ServiceNotFoundException;

class ConfigurationTest extends TestCase
{
    use ConfigurationTestCaseTrait;

    #[Test]
    #[DataProvider('provideValidConfig')]
    public function validTest(array $configs): void
    {
        $this->assertConfigurationIsValid($configs);

        $extension = $this->getExtension();

        $builder = new ContainerBuilder();
        $extension->load($configs, $builder);

        $configProviderDefinition = $builder->getDefinition(ConfigProvider::class);

        self::assertSame($configs[0]['countryCode'], $configProviderDefinition->getArgument(0));
    }

    #[Test]
    #[DataProvider('provideInvalidConfig')]
    public function invalid(array $config, ?string $expectedMessage = null): void
    {
        $this->assertConfigurationIsInvalid(
            $config,
            $expectedMessage
        );
    }

    #[Test]
    #[DataProvider('provideMoreInvalidConfig')]
    public function moreInvalidTest(array $configs, \Throwable $expectedException): void
    {
        $this->expectException(get_class($expectedException));
        $this->expectExceptionMessage($expectedException->getMessage());

        $this->assertConfigurationIsValid($configs);

        $extension = $this->getExtension();

        $builder = new ContainerBuilder();
        $extension->load($configs, $builder);
    }

    public static function provideInvalidConfig(): iterable
    {
        yield [
            [
                [],
            ],
            '"answear_gls" must be configured.',
        ];

        yield [
            [
                [
                    'countryCode' => '',
                ],
            ],
            'The value "" is not allowed for path',
        ];

        yield [
            [
                [
                    'countryCode' => 'test',
                ],
            ],
            'The value "test" is not allowed for path "answear_gls.countryCode"',
        ];

        yield [
            [
                [
                    'countryCode' => 'sk',
                ],
            ],
            'The value "sk" is not allowed for path "answear_gls.countryCode"',
        ];
    }

    public static function provideMoreInvalidConfig(): iterable
    {
        yield [
            [
                [
                    'countryCode' => 'SK',
                    'logger' => 'not-existing-service-name',
                ],
            ],
            new ServiceNotFoundException('not-existing-service-name'),
        ];
    }

    public static function provideValidConfig(): iterable
    {
        yield [
            [
                [
                    'countryCode' => 'SI',
                ],
            ],
        ];

        yield [
            [
                [
                    'countryCode' => 'SK',
                ],
            ],
        ];
    }

    protected function getContainerExtensions(): array
    {
        return [$this->getExtension()];
    }

    protected function getConfiguration(): Configuration
    {
        return new Configuration();
    }

    private function getExtension(): AnswearGlsExtension
    {
        return new AnswearGlsExtension();
    }
}
