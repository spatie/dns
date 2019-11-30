<?php

namespace Spatie\Dns\Test;

use PHPUnit\Framework\TestCase;
use Spatie\Dns\Dns;
use Spatie\Dns\Exceptions\CouldNotFetchDns;
use Spatie\Dns\Exceptions\InvalidArgument;

class DnsTest extends TestCase
{
    protected Dns $dns;

    protected function setUp(): void
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

    /** @test */
    public function it_can_set_the_use_name_server()
    {
        $this->assertEquals('dns.spatie.be', (new Dns('https://spatie.be'))->useNameServer('dns.spatie.be')->getNameServer());
    }

    /** @test */
    public function it_throws_exception_on_failed_to_fetch_dns_record()
    {
        $this->expectException(CouldNotFetchDns::class);
        $this->expectExceptionMessage("Dig command failed with message: `dig: couldn't get address for 'dns.spatie.be': not found`");
        (new Dns('https://spatie.be'))->useNameServer('dns.spatie.be')->getRecords('MX');
    }

    protected function assertSeeRecordTypes($records, array $types)
    {
        foreach ($types as $type) {
            //some dns servers use tabs, let's replace them by spaces
            $records = preg_replace('/\s+/', ' ', $records);

            $this->assertStringContainsString("IN {$type}", $records);
        }
    }

    protected function assertDontSeeRecordTypes($records, array $types)
    {
        foreach ($types as $type) {
            //some dns servers use tabs, let's replace them by spaces
            $records = preg_replace('/\s+/', ' ', $records);

            $this->assertStringNotContainsString("IN {$type}", $records);
        }
    }
}
