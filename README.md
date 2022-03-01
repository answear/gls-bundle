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

//TODO

## Usage

//TODO

### Error handling

- `Answear\GlsBundle\Exception\ServiceUnavailableException` for all `GuzzleException`
- `nswear\GlsBundle\Exception\MalformedResponseException` for partner other errors

Final notes
------------

Feel free to open pull requests with new features, improvements or bug fixes. The Answear team will be grateful for any comments.
