<?php

declare(strict_types=1);

namespace Answear\GlsBundle\Response\DTO;

class Address
{
    private string $zipCode;
    private string $city;
    private string $place;
    private ?string $contact;
    private ?string $phone;
    private ?string $email;

    public function __construct(
        string $zipCode,
        string $city,
        string $place,
        ?string $contact,
        ?string $phone,
        ?string $email
    ) {
        $this->zipCode = $zipCode;
        $this->city = $city;
        $this->place = $place;
        $this->contact = $contact;
        $this->phone = $phone;
        $this->email = $email;
    }

    public static function fromRawParcelShop(RawParcelShop $rawParcelShop): self
    {
        return new self(
            $rawParcelShop->zipcode,
            $rawParcelShop->city,
            $rawParcelShop->address,
            $rawParcelShop->contact,
            $rawParcelShop->phone,
            $rawParcelShop->email
        );
    }

    public function getZipCode(): string
    {
        return $this->zipCode;
    }

    public function getCity(): string
    {
        return $this->city;
    }

    public function getPlace(): string
    {
        return $this->place;
    }

    public function getContact(): ?string
    {
        return $this->contact;
    }

    public function getPhone(): ?string
    {
        return $this->phone;
    }

    public function getEmail(): ?string
    {
        return $this->email;
    }
}
