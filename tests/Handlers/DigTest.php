<?php

use Spatie\Dns\Dns;
use Spatie\Dns\Exceptions\CouldNotFetchDns;
use Spatie\Dns\Handlers\Dig;
use Spatie\Dns\Support\Factory;
use Spatie\Dns\Test\TestClasses\CustomHandler;

beforeEach(function () {
    $this->originalPath = getenv('PATH');
    $this->binDirectory = sys_get_temp_dir().'/spatie-dns-'.uniqid();
    $this->invocationLog = "{$this->binDirectory}/invocations";

    mkdir($this->binDirectory);
    file_put_contents("{$this->binDirectory}/dig", "#!/bin/sh\necho \"\$@\" >> {$this->invocationLog}\n");
    chmod("{$this->binDirectory}/dig", 0755);

    putenv("PATH={$this->binDirectory}:{$this->originalPath}");
});

afterEach(function () {
    putenv("PATH={$this->originalPath}");

    array_map('unlink', glob("{$this->binDirectory}/*"));
    rmdir($this->binDirectory);
});

it('checks a dig binary only once per process', function () {
    expect((new Dig(new Factory))->canHandle())->toBeTrue();
    expect((new Dig(new Factory))->canHandle())->toBeTrue();

    expect(file($this->invocationLog))->toHaveCount(1);
});

it('checks a failing dig binary again on the next lookup', function (string $failure) {
    file_put_contents("{$this->binDirectory}/dig", "#!/bin/sh\necho \"\$@\" >> {$this->invocationLog}\n{$failure}\n");

    expect((new Dig(new Factory))->canHandle())->toBeFalse();
    expect((new Dig(new Factory))->canHandle())->toBeFalse();

    expect(file($this->invocationLog))->toHaveCount(2);
})->with([
    'exit 1',
    'echo "dig: not found"',
]);

it('falls back to native PHP when a previously working dig binary breaks', function () {
    file_put_contents("{$this->binDirectory}/dig", "#!/bin/sh\nif [ \"\$1\" = '-v' ]; then exit 0; fi\necho 'example.invalid. 300 IN A 192.0.2.10'\n");

    $records = Dns::query()->getRecords('example.invalid', DNS_A);

    expect($records)->toHaveCount(1)
        ->and($records[0]->ip())->toBe('192.0.2.10');

    file_put_contents("{$this->binDirectory}/dig", "#!/bin/sh\nexit 1\n");

    expect(Dns::query()->getRecords('example.invalid', DNS_A))->toBe([]);
});

it('respects custom handler order when a previously working dig binary breaks', function () {
    expect(Dns::query()->getRecords('example.invalid', DNS_A))->toBe([]);

    file_put_contents("{$this->binDirectory}/dig", "#!/bin/sh\nexit 1\n");

    $records = Dns::query()
        ->useHandlers([new Dig(new Factory), new CustomHandler])
        ->getRecords('example.invalid', ['A', 'AAAA']);

    expect($records)->toBe(['custom-handler-results-A', 'custom-handler-results-AAAA']);
});

it('does not fall back when dig still works but the query fails', function () {
    expect(Dns::query()->getRecords('example.invalid', DNS_A))->toBe([]);

    file_put_contents("{$this->binDirectory}/dig", "#!/bin/sh\nif [ \"\$1\" = '-v' ]; then exit 0; fi\nexit 9\n");

    expect(fn () => Dns::query()->getRecords('example.invalid', DNS_A))
        ->toThrow(fn (CouldNotFetchDns $exception) => expect($exception->exitCode)->toBe(9));
});
