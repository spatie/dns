<?php

namespace Spatie\Dns\Test\Records;

use PHPUnit\Framework\TestCase;
use Spatie\Dns\Records\SOA;

class SOATest extends TestCase
{
    /** @test */
    public function it_can_parse_string()
    {
        $record = SOA::parse('spatie.be.              82393   IN      SOA     ns1.openprovider.nl. dns.openprovider.eu. 2020100801 10800 3600 604800 3600');

        $this->assertSame('spatie.be', $record->host());
        $this->assertSame(82393, $record->ttl());
        $this->assertSame('IN', $record->class());
        $this->assertSame('SOA', $record->type());
        $this->assertSame('ns1.openprovider.nl', $record->mname());
        $this->assertSame('dns.openprovider.eu', $record->rname());
        $this->assertSame(2020100801, $record->serial());
        $this->assertSame(10800, $record->refresh());
        $this->assertSame(3600, $record->retry());
        $this->assertSame(604800, $record->expire());
        $this->assertSame(3600, $record->minimum_ttl());
    }

    /** @test */
    public function it_can_make_from_array()
    {
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

        $this->assertSame('spatie.be', $record->host());
        $this->assertSame(82393, $record->ttl());
        $this->assertSame('IN', $record->class());
        $this->assertSame('SOA', $record->type());
        $this->assertSame('ns1.openprovider.nl', $record->mname());
        $this->assertSame('dns.openprovider.eu', $record->rname());
        $this->assertSame(2020100801, $record->serial());
        $this->assertSame(10800, $record->refresh());
        $this->assertSame(3600, $record->retry());
        $this->assertSame(604800, $record->expire());
        $this->assertSame(3600, $record->minimum_ttl());
    }

    /** @test */
    public function it_can_transform_to_string()
    {
        $record = SOA::parse('spatie.be.              82393   IN      SOA     ns1.openprovider.nl. dns.openprovider.eu. 2020100801 10800 3600 604800 3600');

        $this->assertSame("spatie.be.\t\t82393\tIN\tSOA\tns1.openprovider.nl.\tdns.openprovider.eu.\t2020100801\t10800\t3600\t604800\t3600", strval($record));
    }

    /** @test */
    public function it_can_be_converted_to_an_array()
    {
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
        $this->assertSame('spatie.be', $data['host']);
        $this->assertSame(82393, $data['ttl']);
        $this->assertSame('IN', $data['class']);
        $this->assertSame('SOA', $data['type']);
        $this->assertSame('ns1.openprovider.nl', $data['mname']);
        $this->assertSame('dns.openprovider.eu', $data['rname']);
        $this->assertSame(2020100801, $data['serial']);
        $this->assertSame(10800, $data['refresh']);
        $this->assertSame(3600, $data['retry']);
        $this->assertSame(604800, $data['expire']);
        $this->assertSame(3600, $data['minimum_ttl']);
    }

    /** @test */
    public function it_return_null_for_to_few_attributes()
    {
        $record = SOA::parse('spatie.be.              82393   IN      SOA     ns1.openprovider.nl. dns.openprovider.eu. 2020100801 10800 3600 604800');

        $this->assertNull($record);
    }
}
