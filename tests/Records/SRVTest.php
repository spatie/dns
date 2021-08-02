<?php

namespace Spatie\Dns\Test\Records;

use PHPUnit\Framework\TestCase;
use Spatie\Dns\Records\SRV;

class SRVTest extends TestCase
{
    /** @test */
    public function it_can_parse_string()
    {
        $record = SRV::parse('_http._tcp.mxtoolbox.com. 3600  IN      SRV     10 100 80 mxtoolbox.com.');

        $this->assertSame('_http._tcp.mxtoolbox.com', $record->host());
        $this->assertSame(3600, $record->ttl());
        $this->assertSame('IN', $record->class());
        $this->assertSame('SRV', $record->type());
        $this->assertSame(10, $record->pri());
        $this->assertSame(100, $record->weight());
        $this->assertSame(80, $record->port());
        $this->assertSame('mxtoolbox.com', $record->target());
    }

    /** @test */
    public function it_can_make_from_array()
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

        $this->assertSame('_http._tcp.mxtoolbox.com', $record->host());
        $this->assertSame(3600, $record->ttl());
        $this->assertSame('IN', $record->class());
        $this->assertSame('SRV', $record->type());
        $this->assertSame(10, $record->pri());
        $this->assertSame(100, $record->weight());
        $this->assertSame(80, $record->port());
        $this->assertSame('mxtoolbox.com', $record->target());
    }

    /** @test */
    public function it_can_transform_to_string()
    {
        $record = SRV::parse('_http._tcp.mxtoolbox.com. 3600  IN      SRV     10 100 80 mxtoolbox.com.');

        $this->assertSame("_http._tcp.mxtoolbox.com.\t\t3600\tIN\tSRV\t10\t100\t80\tmxtoolbox.com.", strval($record));
    }

    /** @test */
    public function it_can_be_converted_to_an_array()
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

        $data = $record->toArray();
        $this->assertSame('_http._tcp.mxtoolbox.com', $data['host']);
        $this->assertSame(3600, $data['ttl']);
        $this->assertSame('IN', $data['class']);
        $this->assertSame('SRV', $data['type']);
        $this->assertSame(10, $data['pri']);
        $this->assertSame(100, $data['weight']);
        $this->assertSame(80, $data['port']);
        $this->assertSame('mxtoolbox.com', $data['target']);
    }

    /** @test */
    public function it_return_null_for_to_few_attributes()
    {
        $record = SRV::parse('_http._tcp.mxtoolbox.com. 3600  IN      SRV     10 100 80');

        $this->assertNull($record);
    }
}
