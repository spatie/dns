<?php

namespace Spatie\Dns\Test;

use PHPUnit\Framework\TestCase;
use Spatie\Dns\Dns;
use Spatie\Dns\Exceptions\InvalidArgument;

class DnsTest extends TestCase
{
    /** @var \Spatie\Dns\Dns  */
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

        $this->assertSeeRecordTypes($records, ['A', 'NS', 'SOA', 'MX', 'TXT']);
    }

    /** @test */
    public function it_fetches_records_for_a_given_single_record_type()
    {
        $records = $this->dns->getRecords('TXT');

        $this->assertSeeRecordTypes($records, ['TXT']);
        $this->assertDontSeeRecordTypes($records, ['A', 'MX']);
    }

    /** @test */
    public function it_fetches_records_for_multiple_given_record_types()
    {
        $records = $this->dns->getRecords('TXT', 'MX');

        $this->assertSeeRecordTypes($records, ['TXT', 'MX']);
        $this->assertDontSeeRecordTypes($records, ['A']);
    }

    /** @test */
    public function it_fetches_records_for_the_types_in_a_given_array()
    {
        $records = $this->dns->getRecords(['TXT', 'MX']);

        $this->assertSeeRecordTypes($records, ['TXT', 'MX']);
        $this->assertDontSeeRecordTypes($records, ['A']);
    }


    /** @test */
    public function it_doesnt_care_about_casing()
    {
        $records = $this->dns->getRecords('TXT');

        $this->assertSeeRecordTypes($records, ['TXT']);

        $records = $this->dns->getRecords('txt');

        $this->assertSeeRecordTypes($records, ['TXT']);
    }

    /** @test */
    public function it_throws_an_exception_if_an_invalid_record_type_is_passed()
    {
        $this->expectException(InvalidArgument::class);

        $this->dns->getRecords('xyz');
    }

    protected function assertSeeRecordTypes($records, $type)
    {
        $types = (array)$type;

        foreach($types as $type) {
            //some dns servers use tabs, let's replace them by spaces
            $records = preg_replace('/\s+/', ' ', $records);

            $this->assertContains("IN {$type}", $records);
        }
    }

    protected function assertDontSeeRecordTypes($records, $type)
    {
        $types = (array)$type;

        foreach($types as $type) {
            //some dns servers use tabs, let's replace them by spaces
            $records = preg_replace('/\s+/', ' ', $records);

            $this->assertNotContains("IN {$type}", $records);
        }
    }
}
