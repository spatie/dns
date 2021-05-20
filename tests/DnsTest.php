<?php

namespace Spatie\Dns\Test;

use PHPUnit\Framework\TestCase;
use Spatie\Dns\Dns;
use Spatie\Dns\Exceptions\CouldNotFetchDns;
use Spatie\Dns\Exceptions\InvalidArgument;
use Spatie\Dns\Records\A;
use Spatie\Dns\Records\MX;
use Spatie\Dns\Records\NS;
use Spatie\Dns\Records\Record;
use Spatie\Dns\Records\SOA;
use Spatie\Dns\Support\Collection;

class DnsTest extends TestCase
{
    protected Dns $dns;

    protected function setUp(): void
    {
        parent::setUp();

        ray()->newScreen($this->getName());

        $this->dns = new Dns();
    }

    /** @test */
    public function it_throws_an_exception_if_an_empty_string_is_passed()
    {
        $this->expectException(InvalidArgument::class);

        $this->dns->getRecords('');
    }

    /** @test */
    public function it_fetches_all_records_by_default()
    {
        $records = $this->dns->getRecords('spatie.be');

        $this->assertSeeRecordTypes(
            $records,
            [A::class, NS::class, SOA::class, MX::class]
        );
    }

    /** @test */
    public function it_fetches_all_records_with_asterisk()
    {
        $records = $this->dns->getRecords('spatie.be', '*');

        $this->assertSeeRecordTypes(
            $records,
            [A::class, NS::class, SOA::class, MX::class]
        );
    }

    /** @test */
    public function it_fetches_records_for_a_single_type_via_flag()
    {
        $records = $this->dns->getRecords('spatie.be', DNS_NS);

        $this->assertOnlySeeRecordTypes($records, [NS::class]);
    }

    /** @test */
    public function it_fetches_records_for_a_single_type_via_name()
    {
        $records = $this->dns->getRecords('spatie.be', 'NS');

        $this->assertOnlySeeRecordTypes($records, [NS::class]);
    }

    /** @test */
    public function it_fetches_records_for_multiple_types_via_flags()
    {
        $records = $this->dns->getRecords('spatie.be', DNS_NS | DNS_SOA);

        $this->assertOnlySeeRecordTypes($records, [NS::class, SOA::class]);
    }

    /** @test */
    public function it_fetches_records_for_multiple_types_via_names()
    {
        $records = $this->dns->getRecords('spatie.be', ['NS', 'SOA']);

        $this->assertOnlySeeRecordTypes($records, [NS::class, SOA::class]);
    }

    /** @test */
    public function it_fetches_records_via_name_and_ignores_casing()
    {

        $records = $this->dns->getRecords('spatie.be', 'ns');
        ray()->clearScreen();
ray($records);
        $this->assertOnlySeeRecordTypes($records, [NS::class]);
    }

    /** @test */
    public function it_throws_an_exception_if_an_invalid_record_type_is_passed()
    {
        $this->expectException(InvalidArgument::class);

        $this->dns->getRecords('spatie.be', 'xyz');
    }

    /** @test */
    public function it_uses_provided_nameserver_if_set()
    {
        $this->dns->useNameserver('ns1.openminds.be');

        $this->assertEquals('ns1.openminds.be', $this->dns->getNameserver());
    }

    /** @test */
    public function it_uses_default_nameserver_if_not_set()
    {
        $this->assertNull($this->dns->getNameserver());
    }

    /** @test */
    public function it_throws_exception_on_failed_to_fetch_dns_record()
    {
        $this->expectException(CouldNotFetchDns::class);
        $this->expectExceptionMessage("Dig command failed with message: `dig: couldn't get address for 'dns.spatie.be': not found`");

        $this->dns
            ->useNameserver('dns.spatie.be')
            ->getRecords('spatie.be', DNS_A);
    }

    protected function assertSeeRecordTypes(Collection $records, array $types)
    {
        foreach ($types as $type) {
            $foundRecords = array_filter(
                $records->all(),
                fn (Record $record): bool => is_a($record, $type)
            );

            $this->assertNotEmpty($foundRecords);
        }
    }

    protected function assertDontSeeRecordTypes(Collection $records, array $types)
    {
        foreach ($types as $type) {
            $foundRecords = array_filter(
                $records->all(),
                fn (Record $record): bool => is_a($record, $type)
            );

            $this->assertEmpty($foundRecords);
        }
    }

    protected function assertOnlySeeRecordTypes(Collection $records, array $types)
    {
        $expectedCount = count($records->all());

        $foundRecords = Collection::make($records->all())
            ->filter(fn (Record $record) => $this->recordIsOfType($record, $types));

        $this->assertCount($expectedCount, $foundRecords);
    }

    protected function recordIsOfType(Record $record, array $types): bool
    {
        foreach ($types as $type) {
            if (is_a($record, $type)) {
                return true;
            }
        }

        return false;
    }
}
