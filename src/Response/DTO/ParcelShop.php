<?php

declare(strict_types=1);

namespace Answear\GlsBundle\Response\DTO;

use Answear\GlsBundle\Enum\CountryCode;
use Answear\GlsBundle\Util\BooleanTransformer;

class ParcelShop
{
    private string $shopId;
    private string $name;
    private CountryCode $countryCode;
    private bool $codHandler;
    private bool $payByBankCard;
    private bool $dropOffPoint;
    private string $owner;
    private bool $parcelLocker;
    private ?string $vendorUrl;
    private ?string $pickupTime;
    private ?string $info;
    private Address $address;
    /**
     * @var Openings[]
     */
    private array $openings = [];
    private Coordinates $coordinates;
    private ?string $holidayStarts;
    private ?string $holidayEnds;

    public function __construct(
        string $shopId,
        string $name,
        CountryCode $countryCode,
        bool $isCodHandler,
        bool $payByBankCard,
        bool $dropOffPoint,
        string $owner,
        bool $isParcelLocker,
        ?string $vendorUrl,
        ?string $pickupTime,
        ?string $info,
        Address $address,
        array $openings,
        Coordinates $coordinates,
        ?string $holidayStarts,
        ?string $holidayEnds
    ) {
        $this->shopId = $shopId;
        $this->name = $name;
        $this->countryCode = $countryCode;
        $this->codHandler = $isCodHandler;
        $this->payByBankCard = $payByBankCard;
        $this->dropOffPoint = $dropOffPoint;
        $this->owner = $owner;
        $this->parcelLocker = $isParcelLocker;
        $this->vendorUrl = $vendorUrl;
        $this->pickupTime = $pickupTime;
        $this->info = $info;
        $this->address = $address;
        $this->openings = $openings;
        $this->coordinates = $coordinates;
        $this->holidayStarts = $holidayStarts;
        $this->holidayEnds = $holidayEnds;
    }

    /**
     * @param Openings[] $openings
     */
    public static function fromRawParcelShop(RawParcelShop $rawParcelShop, array $openings): self
    {
        return new self(
            $rawParcelShop->pclshopid,
            $rawParcelShop->name,
            CountryCode::byValue($rawParcelShop->ctrcode),
            null === $rawParcelShop->iscodhandler ? false : BooleanTransformer::transformToBoolean($rawParcelShop->iscodhandler),
            null === $rawParcelShop->paybybankcard ? false : BooleanTransformer::transformToBoolean($rawParcelShop->paybybankcard),
            null === $rawParcelShop->dropoffpoint ? false : BooleanTransformer::transformToBoolean($rawParcelShop->dropoffpoint),
            $rawParcelShop->owner,
            null === $rawParcelShop->isparcellocker ? false : BooleanTransformer::transformToBoolean($rawParcelShop->isparcellocker),
            $rawParcelShop->vendor_url,
            $rawParcelShop->pcl_pickup_time,
            $rawParcelShop->info,
            Address::fromRawParcelShop($rawParcelShop),
            $openings,
            new Coordinates((float) $rawParcelShop->geolat, (float) $rawParcelShop->geolng),
            $rawParcelShop->holidaystarts,
            $rawParcelShop->holidayends
        );
    }

    public function getShopId(): string
    {
        return $this->shopId;
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function getCountryCode(): CountryCode
    {
        return $this->countryCode;
    }

    public function isCodHandler(): bool
    {
        return $this->codHandler;
    }

    public function hasPayByBankCard(): bool
    {
        return $this->payByBankCard;
    }

    public function isDropOffPoint(): bool
    {
        return $this->dropOffPoint;
    }

    public function getOwner(): string
    {
        return $this->owner;
    }

    public function isParcelLocker(): bool
    {
        return $this->parcelLocker;
    }

    public function getVendorUrl(): ?string
    {
        return $this->vendorUrl;
    }

    public function getPickupTime(): ?string
    {
        return $this->pickupTime;
    }

    public function getInfo(): ?string
    {
        return $this->info;
    }

    public function getAddress(): Address
    {
        return $this->address;
    }

    /**
     * @return Openings[]
     */
    public function getOpenings(): array
    {
        return $this->openings;
    }

    public function getCoordinates(): Coordinates
    {
        return $this->coordinates;
    }

    public function getHolidayStarts(): ?string
    {
        return $this->holidayStarts;
    }

    public function getHolidayEnds(): ?string
    {
        return $this->holidayEnds;
    }
}
