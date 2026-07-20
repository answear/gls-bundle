# GLS pickup point bundle

GLS pickup point integration for Symfony.

## Installation

* install with Composer

```
composer require git@github.com:answear/gls-bundle.git
```

`Answear\GlsBundle\AnswearGlsBundle::class => ['all' => true],`  
should be added automatically to your `config/bundles.php` file by Symfony Flex.

## Setup

* provide required config data: `countryCode`
* optionally provide credentials for the MyGLS Web Services API (e.g. for Slovakia `GetDeliveryPoints` endpoint):

```yaml
# config/packages/answear_gls.yaml
answear_gls:
    countryCode: HU|SK|CZ|RO|SI|HR
    logger: yourCustomLoggerService #default: null
    username: 'your-mygls-username' #optional
    password: 'your-mygls-password' #optional
    apiUrl: 'https://api.mygls.sk/MasterDataService.svc/json/GetDeliveryPoints' #optional
```

Logger service must implement `Psr\Log\LoggerInterface` interface.

## Usage

### Get ParcelShops

```php
/** @var \Answear\GlsBundle\Service\ParcelShopsService $parcelShopService **/
$parcelShopService->getParcelShopCollection();
```

will return `\Answear\GlsBundle\Response\DTO\ParcelShop[]` array.

### Error handling

- `Answear\GlsBundle\Exception\ServiceUnavailableException` for all `GuzzleException`

Final notes
------------

Feel free to open pull requests with new features, improvements or bug fixes. The Answear team will be grateful for any comments.
