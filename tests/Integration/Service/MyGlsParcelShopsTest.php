<?php

declare(strict_types=1);

namespace Answear\GlsBundle\Tests\Integration\Service;

use Answear\GlsBundle\Client\Client;
use Answear\GlsBundle\Client\RequestTransformer;
use Answear\GlsBundle\ConfigProvider;
use Answear\GlsBundle\Enum\CountryCodeEnum;
use Answear\GlsBundle\Enum\DayTypeEnum;
use Answear\GlsBundle\Enum\FeatureEnum;
use Answear\GlsBundle\Logger\GlsLogger;
use Answear\GlsBundle\Serializer\Serializer;
use Answear\GlsBundle\Service\ParcelShopsService;
use Answear\GlsBundle\Tests\MockGuzzleTrait;
use GuzzleHttp\Psr7\Response;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use Psr\Log\NullLogger;

class MyGlsParcelShopsTest extends TestCase
{
    use MockGuzzleTrait;

    private Serializer $serializer;
    private Client $client;
    private ConfigProvider $configProvider;

    public function setUp(): void
    {
        parent::setUp();

        $this->serializer = new Serializer();
        $this->configProvider = new ConfigProvider(
            CountryCodeEnum::Slovakia->value,
            'test_user',
            'test_password',
            'https://api.mygls.sk/MasterDataService.svc/json/GetDeliveryPoints'
        );
    }

    #[Test]
    public function successfulGetMyGlsParcelShops(): void
    {
        $places = [
            [
                'Id' => 'GLSSK_NEW_VEND',
                'DeliveryPointType' => 1,
                'Latitude' => 48.1485,
                'Longitude' => 17.1077,
                'IsActive' => true,
                'CardPaymentAllowed' => true,
                'CodHandler' => true,
                'Address' => [
                    'Name' => 'New GLS SK ParcelShop',
                    'City' => 'Bratislava',
                    'Street' => 'Obchodna',
                    'HouseNumber' => '10',
                    'ZipCode' => '811 06',
                    'ContactPhone' => '+421900000000',
                    'ContactName' => 'Jan Kowalski',
                ],
                'DeliveryPointOpeningList' => [
                    [
                        'DayOfWeek' => 1,
                        'Open' => '08:00-18:00',
                        'Break' => null,
                    ],
                    [
                        'DayOfWeek' => 2,
                        'Open' => '08:00-18:00',
                        'Break' => '12:00-13:00',
                    ],
                ],
            ],
        ];

        $jsonStr = json_encode($places, JSON_THROW_ON_ERROR);
        $compressed = gzencode($jsonStr);
        $this->assertNotFalse($compressed, 'Failed to gzencode mock data');

        $unpack = unpack('C*', $compressed);
        $this->assertNotFalse($unpack, 'Failed to unpack mock data');

        $dataBytes = array_values($unpack);

        $apiResponse = [
            'ErrorCode' => 0,
            'ErrorDescription' => '',
            'Data' => $dataBytes,
        ];

        $this->client = $this->getClient();
        $this->mockGuzzleResponse(
            new Response(200, [], json_encode($apiResponse, JSON_THROW_ON_ERROR))
        );

        $service = $this->getService();
        $collection = $service->getParcelShopCollection();

        $this->assertCount(1, $collection);

        $shop = $collection[0];
        $this->assertSame('GLSSK_NEW_VEND', $shop->shopId);
        $this->assertSame('New GLS SK ParcelShop', $shop->name);
        $this->assertSame(CountryCodeEnum::Slovakia, $shop->countryCode);
        $this->assertTrue($shop->payByBankCard);
        $this->assertFalse($shop->isParcelLocker);
        $this->assertSame('811 06', $shop->address->zipCode);
        $this->assertSame('Bratislava', $shop->address->city);
        $this->assertSame('Obchodna 10', $shop->address->place);

        $this->assertCount(2, $shop->openings);
        $this->assertSame(DayTypeEnum::Monday, $shop->openings[0]->day);
        $this->assertSame('08:00', $shop->openings[0]->openTime);
        $this->assertSame('18:00', $shop->openings[0]->closeTime);
        $this->assertNull($shop->openings[0]->breakStart);

        $this->assertSame(DayTypeEnum::Tuesday, $shop->openings[1]->day);
        $this->assertSame('08:00', $shop->openings[1]->openTime);
        $this->assertSame('18:00', $shop->openings[1]->closeTime);
        $this->assertSame('12:00', $shop->openings[1]->breakStart);
        $this->assertSame('13:00', $shop->openings[1]->breakEnd);

        $this->assertContains(FeatureEnum::AcceptsCard, $shop->features);
        $this->assertContains(FeatureEnum::AcceptsCash, $shop->features);
    }

    #[Test]
    public function getMyGlsParcelShopsThrowsExceptionOnApiError(): void
    {
        $apiResponse = [
            'ErrorCode' => 100,
            'ErrorDescription' => 'Invalid credentials',
            'Data' => [],
        ];

        $this->client = $this->getClient();
        $this->mockGuzzleResponse(
            new Response(200, [], json_encode($apiResponse, JSON_THROW_ON_ERROR))
        );

        $this->expectException(\RuntimeException::class);
        $this->expectExceptionMessage('MyGLS API error: Invalid credentials (code: 100)');

        $service = $this->getService();
        $service->getParcelShopCollection();
    }

    #[Test]
    public function getMyGlsParcelShopsReturnsEmptyArrayWhenDataIsEmpty(): void
    {
        $apiResponse = [
            'ErrorCode' => 0,
            'ErrorDescription' => '',
            'Data' => [],
        ];

        $this->client = $this->getClient();
        $this->mockGuzzleResponse(
            new Response(200, [], json_encode($apiResponse, JSON_THROW_ON_ERROR))
        );

        $service = $this->getService();
        $collection = $service->getParcelShopCollection();

        $this->assertEmpty($collection);
    }

    private function getClient(): Client
    {
        return new Client(
            new RequestTransformer(
                $this->serializer,
                $this->configProvider
            ),
            new GlsLogger(new NullLogger()),
            $this->setupGuzzleClient()
        );
    }

    private function getService(): ParcelShopsService
    {
        return new ParcelShopsService($this->client, $this->serializer, $this->configProvider);
    }
}
