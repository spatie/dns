<?php

namespace Spatie\Dns\Test;

use PHPUnit\Framework\TestCase;
use Spatie\Dns\Dns;
use Spatie\Dns\Exceptions\CouldNotFetchDns;
use Spatie\Dns\Exceptions\InvalidArgument;
use Spatie\Dns\Contracts\Collection as CollectionContract;
use Spatie\Dns\Records\A;
use Spatie\Dns\Records\AAAA;
use Spatie\Dns\Records\CAA;
use Spatie\Dns\Records\CNAME;
use Spatie\Dns\Records\MX;
use Spatie\Dns\Records\NS;
use Spatie\Dns\Records\Record;
use Spatie\Dns\Records\SOA;
use Spatie\Dns\Records\SRV;
use Spatie\Dns\Records\TXT;

class DnsTest extends TestCase
{
    protected Dns $dns;

    protected function setUp(): void
    {
        parent::setUp();

        $this->dns = new Dns();
    }

    /** @test */
    public function it_throws_an_exception_if_an_empty_string_is_passed(): void
    {
        static::expectException(InvalidArgument::class);

        $this->dns->getRecords('');
    }

    /** @test */
    public function it_fetches_all_records_by_default(): void
    {
        $records = $this->dns->getRecords('spatie.be');

        static::assertSeeRecordTypes(
            $records,
            [A::class, NS::class, SOA::class, MX::class]
        );
    }

    /** @test */
    public function it_fetches_all_records_with_asterisk(): void
    {
        $records = $this->dns->getRecords('spatie.be', '*');

        static::assertSeeRecordTypes(
            $records,
            [A::class, NS::class, SOA::class, MX::class]
        );
    }

    /** @test */
    public function it_fetches_records_for_a_single_type_via_flag(): void
    {
        $records = $this->dns->getRecords('spatie.be', DNS_NS);

        static::assertOnlySeeRecordTypes($records, [NS::class]);
    }

    /** @test */
    public function it_fetches_records_for_a_single_type_via_name(): void
    {
        $records = $this->dns->getRecords('spatie.be', 'NS');

        static::assertOnlySeeRecordTypes($records, [NS::class]);
    }

    /** @test */
    public function it_fetches_records_for_multiple_types_via_flags(): void
    {
        $records = $this->dns->getRecords('spatie.be', DNS_NS | DNS_SOA);

        static::assertOnlySeeRecordTypes($records, [NS::class, SOA::class]);
    }

    /** @test */
    public function it_fetches_records_for_multiple_types_via_names(): void
    {
        $records = $this->dns->getRecords('spatie.be', ['NS', 'SOA']);

        static::assertOnlySeeRecordTypes($records, [NS::class, SOA::class]);
    }

    /** @test */
    public function it_fetches_records_via_name_and_ignores_casing(): void
    {
        $records = $this->dns->getRecords('spatie.be', 'ns');

        static::assertOnlySeeRecordTypes($records, [NS::class]);
    }

    /** @test */
    public function it_throws_an_exception_if_an_invalid_record_type_is_passed(): void
    {
        static::expectException(InvalidArgument::class);

        $this->dns->getRecords('xyz');
    }

    /** @test */
    public function it_uses_provided_nameserver_if_set(): void
    {
        $this->assertEquals('ns1.openminds.be', (new Dns('spatie.be', 'ns1.openminds.be'))->getNameserver());
    }

    /** @test */
    public function it_uses_default_nameserver_if_not_set(): void
    {
        $this->assertEquals('', ($this->dns->getNameserver()));
    }

    /** @test */
    public function it_can_set_the_use_name_server(): void
    {
        $this->assertEquals('dns.spatie.be', (new Dns('https://spatie.be'))->useNameServer('dns.spatie.be')->getNameServer());
    }

    /** @test */
    public function it_throws_exception_on_failed_to_fetch_dns_record(): void
    {
        $this->expectException(CouldNotFetchDns::class);
        $this->expectExceptionMessage("Dig command failed with message: `dig: couldn't get address for 'dns.spatie.be': not found`");
        (new Dns('https://spatie.be'))->useNameServer('dns.spatie.be')->getRecords('MX');
    }

    protected static function assertSeeRecordTypes(CollectionContract $records, array $types): void
    {
        foreach ($types as $type) {
            static::assertNotEmpty(
                array_filter(
                    $records->all(),
                    fn (Record $record): bool => is_a($record, $type)
                )
            );
        }
    }

    protected static function assertDontSeeRecordTypes(CollectionContract $records, array $types): void
    {
        foreach ($types as $type) {
            static::assertEmpty(
                array_filter(
                    $records->all(),
                    fn (Record $record): bool => is_a($record, $type)
                )
            );
        }
    }

    protected static function assertOnlySeeRecordTypes(CollectionContract $records, array $types): void
    {
        static::assertCount(
            count($records->all()),
            array_filter(
                $records->all(),
                function (Record $record) use ($types): bool {
                    foreach ($types as $type) {
                        if(is_a($record, $type)) {
                            return true;
                        }
                    }

                    return false;
                }
            )
        );
    }
}
