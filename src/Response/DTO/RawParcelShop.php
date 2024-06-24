<?php

declare(strict_types=1);

namespace Answear\GlsBundle\Response\DTO;

class RawParcelShop
{
    public string $pclshopid;
    public string $name;
    public string $ctrcode;
    public string $zipcode;
    public string $city;
    public string $address;
    public ?string $contact;
    public ?string $phone;
    public ?string $email;
    public ?string $iscodhandler;
    public ?string $paybybankcard;
    public ?string $dropoffpoint;
    public ?string $geolat;
    public ?string $geolng;
    public string $owner;
    public ?string $isparcellocker;
    public ?string $vendor_url;
    public ?string $pcl_pickup_time;
    public ?string $info;
    public ?string $holidaystarts;
    public ?string $holidayends;
}
