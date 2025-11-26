<?php

use Spatie\Dns\Support\Domain;

it('can get a sanitized version of the domain name', function () {
    expect(strval(new Domain('spatie.be')))->toBe('spatie.be');
    expect(strval(new Domain('www.spatie.be')))->toBe('www.spatie.be');
    expect(strval(new Domain('http://spatie.be')))->toBe('spatie.be');
    expect(strval(new Domain('https://spatie.be/page')))->toBe('spatie.be');
    expect(strval(new Domain('https://SPATIE.be')))->toBe('spatie.be');
    expect(strval(new Domain('https://www.SPATIE.be')))->toBe('www.spatie.be');
    expect(strval(new Domain('ftp://ftp.spatie.be')))->toBe('ftp.spatie.be');
    expect(strval(new Domain('freek@spatie.be')))->toBe('spatie.be');
    expect(strval(new Domain('freek+dns@spatie.be')))->toBe('spatie.be');
    expect(strval(new Domain('freek.dns@spatie.be')))->toBe('spatie.be');
});
