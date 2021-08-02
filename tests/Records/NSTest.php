<?php

namespace Spatie\Dns\Test\Records;

use PHPUnit\Framework\TestCase;
use Spatie\Dns\Records\NS;

class NSTest extends TestCase
{
    /** @test */
    public function it_can_parse_string()
    {
        $record = NS::parse('spatie.be.              82516   IN      NS      ns1.openprovider.nl.');

        $this->assertSame('spatie.be', $record->host());
        $this->assertSame(82516, $record->ttl());
        $this->assertSame('IN', $record->class());
        $this->assertSame('NS', $record->type());
        $this->assertSame('ns1.openprovider.nl', $record->target());
    }

    /** @test */
    public function it_can_make_from_array()
    {
        $record = NS::make([
            'host' => 'spatie.be',
            'class' => 'IN',
            'ttl' => 82516,
            'type' => 'NS',
            'target' => 'ns1.openprovider.nl',
        ]);

        $this->assertSame('spatie.be', $record->host());
        $this->assertSame(82516, $record->ttl());
        $this->assertSame('IN', $record->class());
        $this->assertSame('NS', $record->type());
        $this->assertSame('ns1.openprovider.nl', $record->target());
    }

    /** @test */
    public function it_can_transform_to_string()
    {
        $record = NS::parse('spatie.be.              82516   IN      NS      ns1.openprovider.nl.');

        $this->assertSame("spatie.be.\t\t82516\tIN\tNS\tns1.openprovider.nl.", strval($record));
    }

    /** @test */
    public function it_can_be_converted_to_an_array()
    {
        $record = NS::make([
            'host' => 'spatie.be',
            'class' => 'IN',
            'ttl' => 82516,
            'type' => 'NS',
            'target' => 'ns1.openprovider.nl',
        ]);

        $data = $record->toArray();
        $this->assertSame('spatie.be', $data['host']);
        $this->assertSame(82516, $data['ttl']);
        $this->assertSame('IN', $data['class']);
        $this->assertSame('NS', $data['type']);
        $this->assertSame('ns1.openprovider.nl', $data['target']);
    }

    /** @test */
    public function it_does_not_throw_a_warning_for_insufficient_attributes()
    {
        $record = NS::parse('spatie.be.              82516   IN      NS');

        $this->assertSame('', $record->target());
    }
}
