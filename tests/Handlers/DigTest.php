<?php

use Spatie\Dns\Handlers\Dig;
use Spatie\Dns\Support\Factory;

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
    $factory = new Factory;

    expect((new Dig($factory))->canHandle())->toBeTrue();
    expect((new Dig($factory))->canHandle())->toBeTrue();

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
