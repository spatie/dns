# Retrieve DNS records

[![Latest Version on Packagist](https://img.shields.io/packagist/v/spatie/dns.svg?style=flat-square)](https://packagist.org/packages/spatie/dns)
[![Build Status](https://img.shields.io/travis/spatie/dns/master.svg?style=flat-square)](https://travis-ci.org/spatie/dns)
[![StyleCI](https://styleci.io/repos/108810419/shield?branch=master)](https://styleci.io/repos/108810419)
[![Quality Score](https://img.shields.io/scrutinizer/g/spatie/dns.svg?style=flat-square)](https://scrutinizer-ci.com/g/spatie/dns)
[![Total Downloads](https://img.shields.io/packagist/dt/spatie/dns.svg?style=flat-square)](https://packagist.org/packages/spatie/dns)

This package contains a class that can fetch DNS records.

```php
$dns = new Spatie\Dns\Dns('spatie.be');

$dns->getRecords(); // returns all available dns records

$dns->getRecords('A'); // returns only A records
```

## Installation

You can install the package via composer:

```bash
composer require spatie/dns
```

## Usage

The class can get these record types: `A`, `AAAA`, `CNAME`, `NS`, `SOA`, `MX`, `SRV`, `TXT`, `DNSKEY`, `CAA`, `NAPTR`.

``` php
$dns = new Spatie\Dns\Dns('spatie.be');

$dns->getRecords(); // returns all records

$dns->getRecords('A'); // returns only A records
$dns->getRecords('MX'); // returns only MX records

$dns->getRecords('A', 'MX'); // returns both A and MX records
$dns->getRecords(['A', 'MX']); // returns both A and MX records
```

### Testing

``` bash
composer test
```

### Changelog

Please see [CHANGELOG](CHANGELOG.md) for more information what has changed recently.

## Contributing

Please see [CONTRIBUTING](CONTRIBUTING.md) for details.

### Security

If you discover any security related issues, please email freek@spatie.be instead of using the issue tracker.

## Postcardware

You're free to use this package, but if it makes it to your production environment we highly appreciate you sending us a postcard from your hometown, mentioning which of our package(s) you are using.

Our address is: Spatie, Samberstraat 69D, 2060 Antwerp, Belgium.

We publish all received postcards [on our company website](https://spatie.be/en/opensource/postcards).

## Credits

- [Harish Toshniwal](https://github.com/introwit)
- [Freek Van der Herten](https://github.com/freekmurze)
- [All Contributors](../../contributors)

## Support us

Spatie is a webdesign agency based in Antwerp, Belgium. You'll find an overview of all our open source projects [on our website](https://spatie.be/opensource).

Does your business depend on our contributions? Reach out and support us on [Patreon](https://www.patreon.com/spatie). 
All pledges will be dedicated to allocating workforce on maintenance and new awesome stuff.

## License

The MIT License (MIT). Please see [License File](LICENSE.md) for more information.
