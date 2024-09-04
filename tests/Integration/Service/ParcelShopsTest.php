<?php

declare(strict_types=1);

namespace Answear\GlsBundle\Tests\Integration\Service;

use Answear\GlsBundle\Client\Client;
use Answear\GlsBundle\Client\RequestTransformer;
use Answear\GlsBundle\ConfigProvider;
use Answear\GlsBundle\Enum\CountryCodeEnum;
use Answear\GlsBundle\Enum\FeatureEnum;
use Answear\GlsBundle\Logger\GlsLogger;
use Answear\GlsBundle\Response\DTO\Openings;
use Answear\GlsBundle\Response\DTO\ParcelShop;
use Answear\GlsBundle\Serializer\Serializer;
use Answear\GlsBundle\Service\ParcelShopsService;
use Answear\GlsBundle\Tests\MockGuzzleTrait;
use Answear\GlsBundle\Tests\Util\FileTestUtil;
use GuzzleHttp\Psr7\Response;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use Psr\Log\LoggerInterface;
use Psr\Log\NullLogger;

class ParcelShopsTest extends TestCase
{
    use MockGuzzleTrait;

    private Serializer $serializer;
    private Client $client;
    private ConfigProvider $configProvider;

    public function setUp(): void
    {
        parent::setUp();

        $this->serializer = new Serializer();
        $this->configProvider = new ConfigProvider(CountryCodeEnum::Slovenia->value);
    }

    #[Test]
    public function successfulGetParcelShop(): void
    {
        $parcelShops = FileTestUtil::decodeJsonFromFile(__DIR__ . '/data/parcelShops.json');
        $this->client = $this->getClient(true, $parcelShops);
        $service = $this->getService();

        $this->mockGuzzleResponse(
            new Response(200, [], FileTestUtil::getFileContents(__DIR__ . '/data/parcelShops.json'))
        );

        $this->assertOfficeSame($service->getParcelShopCollection());
    }

    private function getClient(?bool $withLogger = true, array $parcelShops = []): Client
    {
        return new Client(
            new RequestTransformer(
                $this->serializer,
                new ConfigProvider(CountryCodeEnum::Slovenia->value)
            ),
            new GlsLogger($withLogger ? $this->getLogger() : new NullLogger()),
            $this->setupGuzzleClient()
        );
    }

    private function getService(): ParcelShopsService
    {
        return new ParcelShopsService($this->client, $this->serializer, $this->configProvider);
    }

    /**
     * @param ParcelShop[] $parcelShops
     */
    private function assertOfficeSame(array $parcelShops): void
    {
        $actualData = [];
        foreach ($parcelShops as $parcelShop) {
            $openings = array_map(
                static function (Openings $opening) {
                    return [
                        'number' => $opening->number,
                        'day' => $opening->day->value,
                        'openTime' => $opening->openTime,
                        'closeTime' => $opening->closeTime,
                        'breakStart' => $opening->breakStart,
                        'breakEnd' => $opening->breakEnd,
                    ];
                },
                $parcelShop->openings
            );

            $features = array_map(
                static function (FeatureEnum $feature) {
                    return $feature->value;
                },
                $parcelShop->features,
            );

            $actualData[] = [
                'shopId' => $parcelShop->shopId,
                'name' => $parcelShop->name,
                'countryCode' => $parcelShop->countryCode->value,
                'isCodHandler' => $parcelShop->isCodHandler,
                'payByBankCard' => $parcelShop->payByBankCard,
                'isParcelLocker' => $parcelShop->isParcelLocker,
                'pickupTime' => $parcelShop->pickupTime,
                'info' => $parcelShop->info,
                'address' => [
                    'zipCode' => $parcelShop->address->zipCode,
                    'city' => $parcelShop->address->city,
                    'place' => $parcelShop->address->place,
                    'phone' => $parcelShop->address->phone,
                    'email' => $parcelShop->address->email,
                    'web' => $parcelShop->address->web,
                ],
                'openings' => $openings,
                'coordinates' => [
                    'latitude' => $parcelShop->coordinates->latitude,
                    'longitude' => $parcelShop->coordinates->longitude,
                ],
                'features' => $features,
            ];
        }

        self::assertSame(
            FileTestUtil::decodeJsonFromFile(__DIR__ . '/data/parcelShops_expected_parcels.json'),
            $actualData
        );
    }

    private function getLogger(): LoggerInterface
    {
        $expected = [
            '[GLS_BUNDLE] Request - /data/deliveryPoints/si.json',
            '[GLS_BUNDLE] Response - /data/deliveryPoints/si.json',
        ];

        $invalidMessages = [];
        $logger = $this->createMock(LoggerInterface::class);
        $logger->expects(self::exactly(count($expected)))
            ->method('info')
            ->with(
                $this->callback(
                    static function (string $message) use ($expected, &$invalidMessages) {
                        if (!in_array($message, $expected)) {
                            $invalidMessages[] = $message;
                        }
                        if (!empty($invalidMessages)) {
                            throw new \LogicException(
                                sprintf(
                                    'Expected messages does not contain actual [%s]',
                                    implode(',', $invalidMessages)
                                )
                            );
                        }

                        return true;
                    }
                )
            );

        return $logger;
    }
}
