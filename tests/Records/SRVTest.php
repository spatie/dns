<?php

namespace Spatie\Dns\Test\Records;

use PHPUnit\Framework\TestCase;
use Spatie\Dns\Records\A;
use Spatie\Dns\Records\MX;
use Spatie\Dns\Records\NS;
use Spatie\Dns\Records\SRV;

class SRVTest extends TestCase
{
    /** @test */
    public function it_can_parse_string(): void
    {
        $record = SRV::parse('_http._tcp.mxtoolbox.com. 3600  IN      SRV     10 100 80 mxtoolbox.com.');

        static::assertSame('_http._tcp.mxtoolbox.com', $record->host());
        static::assertSame(3600, $record->ttl());
        static::assertSame('IN', $record->class());
        static::assertSame('SRV', $record->type());
        static::assertSame(10, $record->pri());
        static::assertSame(100, $record->weight());
        static::assertSame(80, $record->port());
        static::assertSame('mxtoolbox.com', $record->target());
    }

    /** @test */
    public function it_can_make_from_array(): void
    {
        $record = SRV::make([
            'host' => '_http._tcp.mxtoolbox.com',
            'class' => 'IN',
            'ttl' => 3600,
            'type' => 'SRV',
            'pri' => 10,
            'weight' => 100,
            'port' => 80,
            'target' => 'mxtoolbox.com',
        ]);

        static::assertSame('_http._tcp.mxtoolbox.com', $record->host());
        static::assertSame(3600, $record->ttl());
        static::assertSame('IN', $record->class());
        static::assertSame('SRV', $record->type());
        static::assertSame(10, $record->pri());
        static::assertSame(100, $record->weight());
        static::assertSame(80, $record->port());
        static::assertSame('mxtoolbox.com', $record->target());
    }

    /** @test */
    public function it_can_transform_to_string(): void
    {
        $record = SRV::parse('_http._tcp.mxtoolbox.com. 3600  IN      SRV     10 100 80 mxtoolbox.com.');

        static::assertSame("_http._tcp.mxtoolbox.com.\t\t3600\tIN\tSRV\t10\t100\t80\tmxtoolbox.com.", strval($record));
    }
}
