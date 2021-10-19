<?php

namespace Spatie\Dns\Test\Support;

use PHPUnit\Framework\TestCase;
use Spatie\Dns\Records\AAAA;
use Spatie\Dns\Records\CNAME;
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
        return [
            [AAAA::class, 'google.com.             900     IN      AAAA    2a00:1450:400e:800::200e'],
            [AAAA::class, 'google.com.  900 IN  AAAA    2a00:1450:400e:800::200e'],
            [CNAME::class, 'www.spatie.be.       300     IN      CNAME   spatie.be.'],
        ];
    }
}
