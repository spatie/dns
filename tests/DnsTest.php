<?php

namespace Spatie\Dns\Test;

use Spatie\Dns\Dns;
use PHPUnit\Framework\TestCase;
use Spatie\Dns\Exceptions\InvalidArgument;

class DnsTest extends TestCase
{
    /** @var \Spatie\Dns\Dns */
    protected $dns;

    protected function setUp()
    {
        parent::setUp();

        $this->dns = new Dns('spatie.be');
    }

    /** @test */
    public function it_throws_an_exception_if_an_empty_string_is_passed()
    {
        $this->expectException(InvalidArgument::class);

        new Dns('');
    }

    /** @test */
    public function it_fetches_all_dns_records_for_the_given_domain_name()
    {
        $records = $this->dns->getRecords();

        $this->assertSeeRecordTypes($records, ['A', 'NS', 'SOA', 'MX']);
    }

    /** @test */
    public function it_fetches_records_for_a_given_single_record_type()
    {
        $records = $this->dns->getRecords('NS');

        $this->assertSeeRecordTypes($records, ['NS']);
        $this->assertDontSeeRecordTypes($records, ['A', 'MX']);
    }

    /** @test */
    public function it_fetches_records_for_multiple_given_record_types()
    {
        $records = $this->dns->getRecords('NS', 'MX');

        $this->assertSeeRecordTypes($records, ['NS', 'MX']);
        $this->assertDontSeeRecordTypes($records, ['A']);
    }

    /** @test */
    public function it_fetches_records_for_the_types_in_a_given_array()
    {
        $records = $this->dns->getRecords(['NS', 'MX']);

        $this->assertSeeRecordTypes($records, ['NS', 'MX']);
        $this->assertDontSeeRecordTypes($records, ['A']);
    }

    /** @test */
    public function it_doesnt_care_about_casing()
    {
        $records = $this->dns->getRecords('MX');

        $this->assertSeeRecordTypes($records, ['MX']);

        $records = $this->dns->getRecords('mx');

        $this->assertSeeRecordTypes($records, ['MX']);
    }

    /** @test */
    public function it_throws_an_exception_if_an_invalid_record_type_is_passed()
    {
        $this->expectException(InvalidArgument::class);

        $this->dns->getRecords('xyz');
    }

    /** @test */
    public function it_can_get_a_sanitized_version_of_the_domain_name()
    {
        $this->assertEquals('spatie.be', (new Dns('https://spatie.be'))->getDomain());
        $this->assertEquals('spatie.be', (new Dns('https://spatie.be/page'))->getDomain());
        $this->assertEquals('spatie.be', (new Dns('https://SPATIE.be'))->getDomain());
    }

    /** @test */
    public function it_uses_provided_nameserver_if_set()
    {
        $this->assertEquals('ns1.openminds.be', (new Dns('spatie.be', 'ns1.openminds.be'))->getNameserver());
    }

    /** @test */
    public function it_uses_default_nameserver_if_not_set()
    {
        $this->assertEquals('', ($this->dns->getNameserver()));
    }

    protected function assertSeeRecordTypes($records, $type)
    {
        $types = (array) $type;

        foreach ($types as $type) {
            //some dns servers use tabs, let's replace them by spaces
            $records = preg_replace('/\s+/', ' ', $records);

            $this->assertContains("IN {$type}", $records);
        }
    }

    protected function assertDontSeeRecordTypes($records, $type)
    {
        $types = (array) $type;

        foreach ($types as $type) {
            //some dns servers use tabs, let's replace them by spaces
            $records = preg_replace('/\s+/', ' ', $records);

            $this->assertNotContains("IN {$type}", $records);
        }
    }
}
