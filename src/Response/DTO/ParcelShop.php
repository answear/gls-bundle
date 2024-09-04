<?php

declare(strict_types=1);

namespace Answear\GlsBundle\Response\DTO;

use Answear\GlsBundle\Enum\CountryCodeEnum;
use Answear\GlsBundle\Enum\FeatureEnum;
use Answear\GlsBundle\Enum\ParcelShopTypeEnum;

class ParcelShop
{
    /**
     * @param Openings[] $openings
     */
    public function __construct(
        public string $shopId,
        public string $name,
        public CountryCodeEnum $countryCode,
        public bool $isCodHandler,
        public bool $payByBankCard,
        public bool $isParcelLocker,
        public ?string $pickupTime,
        public ?string $info,
        public Address $address,
        public array $openings,
        public Coordinates $coordinates,
        public array $features,
    ) {
    }

    public static function fromRawParcelShop(RawParcelShop $rawParcelShop): self
    {
        $isCodHandler = in_array(FeatureEnum::AcceptsCash->value, $rawParcelShop->features, true);
        $canPayByBankCard = in_array(FeatureEnum::AcceptsCard->value, $rawParcelShop->features, true);
        $isParcelLocker = $rawParcelShop->type === ParcelShopTypeEnum::ParcelLocker->value;
        $contact = Address::fromResponse($rawParcelShop->contact);
        $coordinates = Coordinates::fromResponse($rawParcelShop->location);
        $openings = array_map(
            static fn(array $openings) => Openings::fromResponse($openings),
            $rawParcelShop->hours,
        );
        $countryCode = CountryCodeEnum::from($contact->countryCode);
        $features = array_map(
            static fn(string $feature) => FeatureEnum::tryFrom($feature),
            $rawParcelShop->features,
        );

        return new self(
            $rawParcelShop->id,
            $rawParcelShop->name,
            $countryCode,
            $isCodHandler,
            $canPayByBankCard,
            $isParcelLocker,
            $rawParcelShop->pickupTime,
            $rawParcelShop->description,
            $contact,
            $openings,
            $coordinates,
            array_filter($features),
        );
    }
}
