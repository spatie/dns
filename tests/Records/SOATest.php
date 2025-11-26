<?php

use Spatie\Dns\Records\SOA;

it('can parse string', function () {
    $record = SOA::parse('spatie.be.              82393   IN      SOA     ns1.openprovider.nl. dns.openprovider.eu. 2020100801 10800 3600 604800 3600');

    expect($record->host())->toBe('spatie.be');
    expect($record->ttl())->toBe(82393);
    expect($record->class())->toBe('IN');
    expect($record->type())->toBe('SOA');
    expect($record->mname())->toBe('ns1.openprovider.nl');
    expect($record->rname())->toBe('dns.openprovider.eu');
    expect($record->serial())->toBe(2020100801);
    expect($record->refresh())->toBe(10800);
    expect($record->retry())->toBe(3600);
    expect($record->expire())->toBe(604800);
    expect($record->minimum_ttl())->toBe(3600);
});

it('can make from array', function () {
    $record = SOA::make([
        'host' => 'spatie.be',
        'class' => 'IN',
        'ttl' => 82393,
        'type' => 'SOA',
        'mname' => 'ns1.openprovider.nl',
        'rname' => 'dns.openprovider.eu',
        'serial' => 2020100801,
        'refresh' => 10800,
        'retry' => 3600,
        'expire' => 604800,
        'minimum_ttl' => 3600,
    ]);

    expect($record->host())->toBe('spatie.be');
    expect($record->ttl())->toBe(82393);
    expect($record->class())->toBe('IN');
    expect($record->type())->toBe('SOA');
    expect($record->mname())->toBe('ns1.openprovider.nl');
    expect($record->rname())->toBe('dns.openprovider.eu');
    expect($record->serial())->toBe(2020100801);
    expect($record->refresh())->toBe(10800);
    expect($record->retry())->toBe(3600);
    expect($record->expire())->toBe(604800);
    expect($record->minimum_ttl())->toBe(3600);
});

it('can transform to string', function () {
    $record = SOA::parse('spatie.be.              82393   IN      SOA     ns1.openprovider.nl. dns.openprovider.eu. 2020100801 10800 3600 604800 3600');

    expect(strval($record))->toBe("spatie.be.\t\t82393\tIN\tSOA\tns1.openprovider.nl.\tdns.openprovider.eu.\t2020100801\t10800\t3600\t604800\t3600");
});

it('can be converted to an array', function () {
    $record = SOA::make([
        'host' => 'spatie.be',
        'class' => 'IN',
        'ttl' => 82393,
        'type' => 'SOA',
        'mname' => 'ns1.openprovider.nl',
        'rname' => 'dns.openprovider.eu',
        'serial' => 2020100801,
        'refresh' => 10800,
        'retry' => 3600,
        'expire' => 604800,
        'minimum_ttl' => 3600,
    ]);

    $data = $record->toArray();
    expect($data['host'])->toBe('spatie.be');
    expect($data['ttl'])->toBe(82393);
    expect($data['class'])->toBe('IN');
    expect($data['type'])->toBe('SOA');
    expect($data['mname'])->toBe('ns1.openprovider.nl');
    expect($data['rname'])->toBe('dns.openprovider.eu');
    expect($data['serial'])->toBe(2020100801);
    expect($data['refresh'])->toBe(10800);
    expect($data['retry'])->toBe(3600);
    expect($data['expire'])->toBe(604800);
    expect($data['minimum_ttl'])->toBe(3600);
});

it('returns null for too few attributes', function () {
    $record = SOA::parse('spatie.be.              82393   IN      SOA     ns1.openprovider.nl. dns.openprovider.eu. 2020100801 10800 3600 604800');

    expect($record)->toBeNull();
});
