# Retrieve DNS records

[![Latest Version on Packagist](https://img.shields.io/packagist/v/spatie/dns.svg?style=flat-square)](https://packagist.org/packages/spatie/dns)
[![Tests](https://github.com/spatie/dns/actions/workflows/tests.yml/badge.svg)](https://github.com/spatie/dns/actions/workflows/tests.yml)
[![Code style](https://github.com/spatie/dns/actions/workflows/php-cs-fixer.yml/badge.svg)](https://github.com/spatie/dns/actions/workflows/php-cs-fixer.yml)
[![Total Downloads](https://img.shields.io/packagist/dt/spatie/dns.svg?style=flat-square)](https://packagist.org/packages/spatie/dns)

This package contains a class that can fetch DNS records.

```php
use Spatie\Dns\Dns;

$dns = new Dns();

$dns->getRecords('spatie.be'); // returns all available dns records

$dns->getRecords('spatie.be', 'A'); // returns only A records
```

You can use various methods to retrieve info of a record.

```php
$records = $dns->getRecords('spatie.be')

$hostNameOfFirstRecord = $records[0]->host();
```

## Support us

[<img src="https://github-ads.s3.eu-central-1.amazonaws.com/dns.jpg?t=1" width="419px" />](https://spatie.be/github-ad-click/dns)

We invest a lot of resources into creating [best in class open source packages](https://spatie.be/open-source). You can support us by [buying one of our paid products](https://spatie.be/open-source/support-us).

We highly appreciate you sending us a postcard from your hometown, mentioning which of our package(s) you are using. You'll find our address on [our contact page](https://spatie.be/about-us). We publish all received postcards on [our virtual postcard wall](https://spatie.be/open-source/postcards).

## Installation

If you do not have [dig](https://linux.die.net/man/1/dig) installed you will need it.

You can install the package via composer:

```bash
composer require spatie/dns
```

## Usage

The class can get these record types: `A`, `AAAA`, `CNAME`, `NS`, `PTR`, `SOA`, `MX`, `SRV`, `TXT`, `DNSKEY`, `CAA`, `NAPTR`.

```php
use Spatie\Dns\Dns;

$dns = new Dns();

$dns->getRecords('spatie.be'); // returns all available dns records

$dns->getRecords('spatie.be', 'A'); // returns only A records
$dns->getRecords('spatie.be', ['A', 'CNAME']); // returns both A and CNAME records
$dns->getRecords('spatie.be', DNS_MX); // returns only MX records
$dns->getRecords('spatie.be', DNS_A | DNS_AAAA); // returns both A and AAAA records
```

`getRecords` will return an array with objects that implement the `Spatie\Dns\Records\Record` interface.

## Working with DNS records

Here's how you can fetch the first A-record of a domain.

```php
$ARecord = $dns->getRecords('spatie.be', 'A')[0];
```

These methods can be called on all records:

- `host()`: returns the host (`spatie.be`)
- `ttl()`: return the time to live (`900`)
- `class()`: returns the class (`IN`)
- `type()`: returns the type (`A`)

When converting a record to a string you'll get a string with all info separated with tabs.

```php
(string)$ARecord // returns `spatie.be.              900     IN      A       138.197.187.74`
```

Some records have additional methods available. For example, records of type A [have an additional `ip()` method](https://github.com/spatie/dns/blob/72bf709a44e19e5d8f0bc7e6c93cf70e7a1b18f3/src/Records/A.php#L6). To know which extra methods there are, check the docblocks above [all record classes](https://github.com/spatie/dns/tree/72bf709a44e19e5d8f0bc7e6c93cf70e7a1b18f3/src/Records) in the source code.

## Using a specific nameserver

You can get records from a specific nameserver.

```php
use Spatie\Dns\Dns;

(new Dns)
    ->useNameserver('ns1.openminds.be') // use ns1.openminds.be
    ->getRecords('spatie.be');
```

## Guessing a record

When you have a string that contains a dns record, you can convert it to a `Record`

```php
use \Spatie\Dns\Support\Factory();

// returns instance of \Spatie\Dns\Records\CNAME
(new Factory())->guess('www.spatie.be.       300     IN      CNAME   spatie.be.');
```

## Using custom handlers

A `Handler` is responsible for fetching DNS records of a certain type.

By default, these handlers that ship with the package are used:

- `Spatie\Dns\Handler\Dig`
- `Spatie\Dns\Handler\DnsGetRecord`

You can create custom handlers. A valid handler is any class that extends from `Spatie\Dns\Handler\Handler`.

A custom handler class can be used by passing it to `useHandlers` on `Spatie\Dns\Dns`.

```php
$results = $this->dns
    ->useHandlers([new YourCustomHandler()])
    ->getRecords('spatie.be');
```

## Under the hood

We will use [dig](https://wiki.ubuntuusers.de/dig/) to fetch DNS info. If it is not installed on your system, we'll call the native `dns_get_record()` function.

### Testing

``` bash
composer test
```

### Changelog

Please see [CHANGELOG](CHANGELOG.md) for more information what has changed recently.

## Contributing

Please see [CONTRIBUTING](https://github.com/spatie/.github/blob/main/CONTRIBUTING.md) for details.

### Security

If you've found a bug regarding security please mail [security@spatie.be](mailto:security@spatie.be).

## Postcardware

You're free to use this package, but if it makes it to your production environment we highly appreciate you sending us a postcard from your hometown, mentioning which of our package(s) you are using.

Our address is: Spatie, Kruikstraat 22, 2018 Antwerp, Belgium.

We publish all received postcards [on our company website](https://spatie.be/en/opensource/postcards).

## Credits

- [Harish Toshniwal](https://github.com/introwit)
- [Tom Witkowski](https://github.com/Gummibeer)
- [Freek Van der Herten](https://github.com/freekmurze)
- [All Contributors](../../contributors)

## License

The MIT License (MIT). Please see [License File](LICENSE.md) for more information.
