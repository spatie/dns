<?php

namespace Spatie\Dns\Test\Records;

use PHPUnit\Framework\TestCase;
use Spatie\Dns\Records\AAAA;

class AAAATest extends TestCase
{
    /** @test */
    public function it_can_parse_string()
    {
        $record = AAAA::parse('google.com.             900     IN      AAAA    2a00:1450:400e:800::200e');

        $this->assertSame('google.com', $record->host());
        $this->assertSame(900, $record->ttl());
        $this->assertSame('IN', $record->class());
        $this->assertSame('AAAA', $record->type());
        $this->assertSame('2a00:1450:400e:800::200e', $record->ipv6());
    }

    /** @test */
    public function it_can_make_from_array()
    {
        $record = AAAA::make([
            'host' => 'google.com',
            'class' => 'IN',
            'ttl' => 900,
            'type' => 'AAAA',
            'ipv6' => '2a00:1450:400e:800::200e',
        ]);

        $this->assertSame('google.com', $record->host());
        $this->assertSame(900, $record->ttl());
        $this->assertSame('IN', $record->class());
        $this->assertSame('AAAA', $record->type());
        $this->assertSame('2a00:1450:400e:800::200e', $record->ipv6());
    }

    /** @test */
    public function it_can_transform_to_string()
    {
        $record = AAAA::parse('google.com.             900     IN      AAAA    2a00:1450:400e:800::200e');

        $this->assertSame("google.com.\t\t900\tIN\tAAAA\t2a00:1450:400e:800::200e", strval($record));
    }

    /** @test */
    public function it_can_be_converted_to_an_array()
    {
        $record = AAAA::make([
            'host' => 'google.com',
            'class' => 'IN',
            'ttl' => 900,
            'type' => 'AAAA',
            'ipv6' => '2a00:1450:400e:800::200e',
        ]);

        $data = $record->toArray();
        $this->assertSame('google.com', $data['host']);
        $this->assertSame(900, $data['ttl']);
        $this->assertSame('IN', $data['class']);
        $this->assertSame('AAAA', $data['type']);
        $this->assertSame('2a00:1450:400e:800::200e', $data['ipv6']);
    }

    /** @test */
    public function it_return_null_for_to_few_attributes()
    {
        $record = AAAA::parse('google.com.             900     IN      AAAA');

        $this->assertNull($record);
    }
}
