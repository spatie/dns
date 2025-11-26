<?php

use Spatie\Dns\Records\A;
use Spatie\Dns\Records\AAAA;
use Spatie\Dns\Records\CAA;
use Spatie\Dns\Records\CNAME;
use Spatie\Dns\Records\MX;
use Spatie\Dns\Records\NS;
use Spatie\Dns\Records\PTR;
use Spatie\Dns\Records\SOA;
use Spatie\Dns\Records\SRV;
use Spatie\Dns\Records\TXT;
use Spatie\Dns\Support\Factory;

dataset('dnsRecords', function () {
    $records = [
        [AAAA::class, 'google.com.             900     IN      AAAA    2a00:1450:400e:800::200e'],
        [AAAA::class, 'google.com.  900 IN  AAAA    2a00:1450:400e:800::200e'],
        [A::class, 'spatie.be.              900     IN      A       138.197.187.74'],
        [CNAME::class, 'www.spatie.be.       300     IN      CNAME   spatie.be.'],
        [MX::class, 'spatie.be.              1665    IN      MX      10 ASPMX.L.GOOGLE.COM.'],
        [NS::class, 'spatie.be.              82516   IN      NS      ns1.openprovider.nl.'],
        [PTR::class, '1.73.1.5.in-addr.arpa.              3600     IN      PTR       ae0.452.fra.as205948.creoline.net.'],
        [PTR::class, '1.0.0.0.0.0.0.0.0.0.0.0.0.0.0.0.0.0.0.0.1.0.0.0.0.c.f.6.7.0.a.2.ip6.arpa              3600     IN      PTR       ae0.452.fra.as205948.creoline.net.'],
        [SOA::class, 'spatie.be.              82393   IN      SOA     ns1.openprovider.nl. dns.openprovider.eu. 2020100801 10800 3600 604800 3600'],
        [SRV::class, '_http._tcp.mxtoolbox.com. 3600  IN      SRV     10 100 80 mxtoolbox.com.'],
        [TXT::class, 'spatie.be.              594     IN      TXT     "v=spf1 include:eu.mailgun.org include:spf.factuursturen.be include:sendgrid.net a mx ~all"'],
    ];

    if (defined('DNS_CAA')) {
        $records[] = [CAA::class, 'google.com.             86400   IN      CAA     0 issue "pki.goog"'];
    }

    return $records;
});

it('can guess the record type', function (string $className, string $line) {
    $record = (new Factory())->guess($line);

    expect($record)->toBeInstanceOf($className);
})->with('dnsRecords');

it('will return null when it cannot guess the record', function () {
    expect((new Factory())->guess('invalid-record'))->toBeNull();
});
