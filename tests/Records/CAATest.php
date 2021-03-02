<?php

namespace Spatie\Dns\Test\Records;

use PHPUnit\Framework\TestCase;
use Spatie\Dns\Records\CAA;

class CAATest extends TestCase
{
    /** @test */
    public function it_can_parse_string(): void
    {
        $record = CAA::parse('google.com.             86400   IN      CAA     0 issue "pki.goog"');

        static::assertSame('google.com', $record->host());
        static::assertSame(86400, $record->ttl());
        static::assertSame('IN', $record->class());
        static::assertSame('CAA', $record->type());
        static::assertSame(0, $record->flags());
        static::assertSame('issue', $record->tag());
        static::assertSame('pki.goog', $record->value());
    }

    /** @test */
    public function it_can_make_from_array(): void
    {
        $record = CAA::make([
            'host' => 'google.com',
            'class' => 'IN',
            'ttl' => 86400,
            'type' => 'CAA',
            'flags' => 0,
            'tag' => 'issue',
            'value' => 'pki.goog',
        ]);

        static::assertSame('google.com', $record->host());
        static::assertSame(86400, $record->ttl());
        static::assertSame('IN', $record->class());
        static::assertSame('CAA', $record->type());
        static::assertSame(0, $record->flags());
        static::assertSame('issue', $record->tag());
        static::assertSame('pki.goog', $record->value());
    }

    /** @test */
    public function it_can_transform_to_string(): void
    {
        $record = CAA::parse('google.com.             86400   IN      CAA     0 issue "pki.goog"');

        static::assertSame("google.com.\t\t86400\tIN\tCAA\t0\tissue\t\"pki.goog\"", strval($record));
    }
}
