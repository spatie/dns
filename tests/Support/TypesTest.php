<?php

namespace Spatie\Dns\Test\Support;

use PHPUnit\Framework\TestCase;
use Spatie\Dns\Support\Types;

class TypesTest extends TestCase
{
    protected Types $types;

    protected function setUp(): void
    {
        parent::setUp();

        $this->types = new Types();
    }

    /** @test */
    public function it_can_transform_flag_to_name()
    {
        static::assertSame([DNS_A => 'A'], $this->types->toNames(DNS_A));
        static::assertSame([DNS_AAAA =>'AAAA'], $this->types->toNames(DNS_AAAA));
        static::assertSame([DNS_CAA => 'CAA'], $this->types->toNames(DNS_CAA));
        static::assertSame([DNS_CNAME => 'CNAME'], $this->types->toNames(DNS_CNAME));
        static::assertSame([DNS_MX => 'MX'], $this->types->toNames(DNS_MX));
        static::assertSame([DNS_NS => 'NS'], $this->types->toNames(DNS_NS));
        static::assertSame([DNS_SOA => 'SOA'], $this->types->toNames(DNS_SOA));
        static::assertSame([DNS_SRV => 'SRV'], $this->types->toNames(DNS_SRV));
        static::assertSame([DNS_TXT => 'TXT'], $this->types->toNames(DNS_TXT));
    }

    /** @test */
    public function it_can_transform_flags_to_names()
    {
        static::assertSame([DNS_A => 'A', DNS_AAAA => 'AAAA'], $this->types->toNames(DNS_A | DNS_AAAA));
        static::assertSame([DNS_NS => 'NS', DNS_SOA => 'SOA'], $this->types->toNames(DNS_NS | DNS_SOA));
    }

    /** @test */
    public function it_can_transform_name_to_flag()
    {
        static::assertSame(DNS_A, $this->types->toFlags(['A']));
        static::assertSame(DNS_AAAA, $this->types->toFlags(['AAAA']));
        static::assertSame(DNS_CAA, $this->types->toFlags(['CAA']));
        static::assertSame(DNS_CNAME, $this->types->toFlags(['CNAME']));
        static::assertSame(DNS_MX, $this->types->toFlags(['MX']));
        static::assertSame(DNS_NS, $this->types->toFlags(['NS']));
        static::assertSame(DNS_SOA, $this->types->toFlags(['SOA']));
        static::assertSame(DNS_SRV, $this->types->toFlags(['SRV']));
        static::assertSame(DNS_TXT, $this->types->toFlags(['TXT']));
    }

    /** @test */
    public function it_can_transform_names_to_flags()
    {
        static::assertSame(DNS_A | DNS_AAAA, $this->types->toFlags(['A', 'AAAA']));
        static::assertSame(DNS_NS | DNS_SOA, $this->types->toFlags(['NS', 'SOA']));
        static::assertSame(DNS_A | DNS_AAAA | DNS_CNAME, $this->types->toFlags(['A', 'aaAA', 'cname']));
    }
}
