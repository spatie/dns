<?php

namespace Spatie\Dns\Test\Records;

use PHPUnit\Framework\TestCase;
use Spatie\Dns\Records\AAAA;

class AAAATest extends TestCase
{
    /** @test */
    public function it_can_parse_string(): void
    {
        $record = AAAA::parse('google.com.             900     IN      AAAA    2a00:1450:400e:800::200e');

        static::assertSame('google.com', $record->host());
        static::assertSame(900, $record->ttl());
        static::assertSame('IN', $record->class());
        static::assertSame('AAAA', $record->type());
        static::assertSame('2a00:1450:400e:800::200e', $record->ipv6());
    }

    /** @test */
    public function it_can_make_from_array(): void
    {
        $record = AAAA::make([
            'host' => 'google.com',
            'class' => 'IN',
            'ttl' => 900,
            'type' => 'AAAA',
            'ipv6' => '2a00:1450:400e:800::200e',
        ]);

        static::assertSame('google.com', $record->host());
        static::assertSame(900, $record->ttl());
        static::assertSame('IN', $record->class());
        static::assertSame('AAAA', $record->type());
        static::assertSame('2a00:1450:400e:800::200e', $record->ipv6());
    }

    /** @test */
    public function it_can_transform_to_string(): void
    {
        $record = AAAA::parse('google.com.             900     IN      AAAA    2a00:1450:400e:800::200e');

        static::assertSame("google.com.\t\t900\tIN\tAAAA\t2a00:1450:400e:800::200e", strval($record));
    }
}
