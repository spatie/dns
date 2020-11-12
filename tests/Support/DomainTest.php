<?php

namespace Spatie\Dns\Test\Support;

use PHPUnit\Framework\TestCase;
use Spatie\Dns\Support\Domain;

class DomainTest extends TestCase
{
    /** @test */
    public function it_can_get_a_sanitized_version_of_the_domain_name(): void
    {
        static::assertEquals('spatie.be', strval(new Domain('spatie.be')));
        static::assertEquals('www.spatie.be', strval(new Domain('www.spatie.be')));
        static::assertEquals('spatie.be', strval(new Domain('http://spatie.be')));
        static::assertEquals('spatie.be', strval(new Domain('https://spatie.be/page')));
        static::assertEquals('spatie.be', strval(new Domain('https://SPATIE.be')));
        static::assertEquals('www.spatie.be', strval(new Domain('https://www.SPATIE.be')));
        static::assertEquals('ftp.spatie.be', strval(new Domain('ftp://ftp.spatie.be')));
    }
}
