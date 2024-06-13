<?php

declare(strict_types=1);

namespace Answear\GlsBundle\Tests\Integration\Service;

use Answear\GlsBundle\Client\Client;
use Answear\GlsBundle\Client\RequestTransformer;
use Answear\GlsBundle\ConfigProvider;
use Answear\GlsBundle\Enum\CountryCode;
use Answear\GlsBundle\Logger\GlsLogger;
use Answear\GlsBundle\Response\DTO\ParcelShop;
use Answear\GlsBundle\Serializer\Serializer;
use Answear\GlsBundle\Service\ParcelShopsService;
use Answear\GlsBundle\Tests\MockGuzzleTrait;
use Answear\GlsBundle\Tests\Util\FileTestUtil;
use GuzzleHttp\Psr7\Response;
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
        $this->configProvider = new ConfigProvider(CountryCode::slovenia()->getValue());
    }

    /**
     * @test
     */
    public function successfulGetParcelShop(): void
    {
        $parcelShops = FileTestUtil::decodeJsonFromFile(__DIR__ . '/data/parcelShops.json');
        $this->client = $this->getClient(true, $parcelShops);
        $service = $this->getService();

        $this->mockGuzzleResponse(
            new Response(200, [], FileTestUtil::getFileContents(__DIR__ . '/data/parcelShops.json'))
        );
        foreach ($parcelShops as $element) {
            $this->mockGuzzleResponse(
                new Response(200, [], FileTestUtil::getFileContents(__DIR__ . '/data/openings.json'))
            );
        }

        $this->assertOfficeSame($service->getParcelShopCollection());
    }

    private function getClient(?bool $withLogger = true, array $parcelShops = []): Client
    {
        return new Client(
            new RequestTransformer(
                $this->serializer,
                new ConfigProvider(CountryCode::slovenia()->getValue())
            ),
            new GlsLogger($withLogger ? $this->getLogger($parcelShops) : new NullLogger()),
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
            $address = $parcelShop->getAddress();
            $openings = $parcelShop->getOpenings();

            $actualOpenings = [];
            foreach ($openings as $opening) {
                $actualOpenings[] = [
                    'day' => $opening->getDay()->getValue(),
                    'open' => $opening->getOpen(),
                    'midbreak' => $opening->getMidbreak(),
                ];
            }

            $actualData[] = [
                'shopId' => $parcelShop->getShopId(),
                'name' => $parcelShop->getName(),
                'countryCode' => $parcelShop->getCountryCode()->getValue(),
                'isCodHandler' => $parcelShop->isCodHandler(),
                'payByBankCard' => $parcelShop->hasPayByBankCard(),
                'dropOffPoint' => $parcelShop->isDropOffPoint(),
                'owner' => $parcelShop->getOwner(),
                'isParcelLocker' => $parcelShop->isParcelLocker(),
                'vendorUrl' => $parcelShop->getVendorUrl(),
                'pickupTime' => $parcelShop->getPickupTime(),
                'info' => $parcelShop->getInfo(),
                'address' => [
                    'zipCode' => $address->getZipCode(),
                    'city' => $address->getCity(),
                    'place' => $address->getPlace(),
                    'contact' => $address->getContact(),
                    'phone' => $address->getPhone(),
                    'email' => $address->getEmail(),
                ],
                'openings' => $actualOpenings,
                'coordinates' => null === $parcelShop->getCoordinates() ? null : [
                    'latitude' => $parcelShop->getCoordinates()->latitude,
                    'longitude' => $parcelShop->getCoordinates()->longitude,
                ],
                'holidayStarts' => $parcelShop->getHolidayEnds(),
                'holidayEnds' => $parcelShop->getHolidayEnds(),
            ];
        }

        self::assertSame(
            FileTestUtil::decodeJsonFromFile(__DIR__ . '/data/parcelShops_expected_parcels.json'),
            $actualData
        );
    }

    private function getLogger(array $parcelShops = []): LoggerInterface
    {
        $expected = [
            '[GLS_BUNDLE] Request - /psmap/psmap_getdata.php?action=getList&ctrcode=SI&pclshopin=1&parcellockin=1',
            '[GLS_BUNDLE] Response - /psmap/psmap_getdata.php?action=getList&ctrcode=SI&pclshopin=1&parcellockin=1',
        ];

        foreach ($parcelShops as $parcelShop) {
            $expected[] = '[GLS_BUNDLE] Request - /psmap/psmap_getdata.php?action=getOpenings&pclshopid=' . $parcelShop['pclshopid'];
            $expected[] = '[GLS_BUNDLE] Response - /psmap/psmap_getdata.php?action=getOpenings&pclshopid=' . $parcelShop['pclshopid'];
        }

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
