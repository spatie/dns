<?php

namespace Spatie\Dns\Test\Support;

use PHPUnit\Framework\TestCase;
use Spatie\Dns\Records\A;
use Spatie\Dns\Records\AAAA;
use Spatie\Dns\Records\CAA;
use Spatie\Dns\Records\CNAME;
use Spatie\Dns\Records\MX;
use Spatie\Dns\Records\NS;
use Spatie\Dns\Records\SOA;
use Spatie\Dns\Records\SRV;
use Spatie\Dns\Records\TXT;
use Spatie\Dns\Support\Factory;

class FactoryTest extends TestCase
{
    /**
     * @test
     *
     * @dataProvider dnsRecords
     */
    public function it_can_guess_the_record_type(string $className, string $line)
    {
        $record = (new Factory())->guess($line);

        $this->assertInstanceOf($className, $record);
    }

    public function it_will_return_null_when_it_cannot_guess_the_record()
    {
        $this->assertNull((new Factory())->guess('invalid-record'));
    }

    public function dnsRecords(): array
    {
        $recordTypes = [
            [AAAA::class, 'google.com.             900     IN      AAAA    2a00:1450:400e:800::200e'],
            [AAAA::class, 'google.com.  900 IN  AAAA    2a00:1450:400e:800::200e'],
            [A::class, 'spatie.be.              900     IN      A       138.197.187.74'],
            [CNAME::class, 'www.spatie.be.       300     IN      CNAME   spatie.be.'],
            [MX::class, 'spatie.be.              1665    IN      MX      10 ASPMX.L.GOOGLE.COM.'],
            [NS::class, 'spatie.be.              82516   IN      NS      ns1.openprovider.nl.'],
            [SOA::class, 'spatie.be.              82393   IN      SOA     ns1.openprovider.nl. dns.openprovider.eu. 2020100801 10800 3600 604800 3600'],
            [SRV::class, '_http._tcp.mxtoolbox.com. 3600  IN      SRV     10 100 80 mxtoolbox.com.'],
            [TXT::class, 'spatie.be.              594     IN      TXT     "v=spf1 include:eu.mailgun.org include:spf.factuursturen.be include:sendgrid.net a mx ~all"'],
        ];
        if (defined('DNS_CAA')) {
            $recordTypes[] = [CAA::class, 'google.com.             86400   IN      CAA     0 issue "pki.goog"'];
        }

        return $recordTypes;
    }
}
