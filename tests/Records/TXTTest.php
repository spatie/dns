<?php

namespace Spatie\Dns\Test\Records;

use PHPUnit\Framework\TestCase;
use Spatie\Dns\Records\A;
use Spatie\Dns\Records\MX;
use Spatie\Dns\Records\NS;
use Spatie\Dns\Records\TXT;

class TXTTest extends TestCase
{
    /** @test */
    public function it_can_parse_string(): void
    {
        $record = TXT::parse('spatie.be.              594     IN      TXT     "v=spf1 include:eu.mailgun.org include:spf.factuursturen.be include:sendgrid.net a mx ~all"');

        static::assertSame('spatie.be', $record->host());
        static::assertSame(594, $record->ttl());
        static::assertSame('IN', $record->class());
        static::assertSame('TXT', $record->type());
        static::assertSame('v=spf1 include:eu.mailgun.org include:spf.factuursturen.be include:sendgrid.net a mx ~all', $record->txt());
    }

    /** @test */
    public function it_can_make_from_array(): void
    {
        $record = TXT::make([
            'host' => 'spatie.be',
            'class' => 'IN',
            'ttl' => 594,
            'type' => 'TXT',
            'txt' => 'v=spf1 include:eu.mailgun.org include:spf.factuursturen.be include:sendgrid.net a mx ~all',
        ]);

        static::assertSame('spatie.be', $record->host());
        static::assertSame(594, $record->ttl());
        static::assertSame('IN', $record->class());
        static::assertSame('TXT', $record->type());
        static::assertSame('v=spf1 include:eu.mailgun.org include:spf.factuursturen.be include:sendgrid.net a mx ~all', $record->txt());
    }

    /** @test */
    public function it_can_transform_to_string(): void
    {
        $record = TXT::parse('spatie.be.              594     IN      TXT     "v=spf1 include:eu.mailgun.org include:spf.factuursturen.be include:sendgrid.net a mx ~all"');

        static::assertSame("spatie.be.\t\t594\tIN\tTXT\t\"v=spf1 include:eu.mailgun.org include:spf.factuursturen.be include:sendgrid.net a mx ~all\"", strval($record));
    }
}
