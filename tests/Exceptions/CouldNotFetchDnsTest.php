<?php

use Spatie\Dns\Exceptions\CouldNotFetchDns;
use Symfony\Component\Process\Process;

it('exposes the dig exit code on the exception', function () {
    $process = failedDigProcess(exitCode: 9, stderr: ';; IDN output support not enabled');

    $exception = CouldNotFetchDns::digReturnedWithError($process, 'dig example.com');

    expect($exception->exitCode)->toBe(9);
});

it('leads the message with the dig exit code', function () {
    $process = failedDigProcess(exitCode: 9, stderr: ';; IDN output support not enabled');

    $exception = CouldNotFetchDns::digReturnedWithError($process, 'dig example.com');

    expect($exception->getMessage())->toContain('failed with exit code 9');
});

it('describes known dig exit codes', function () {
    $process = failedDigProcess(exitCode: 9, stderr: ';; IDN output support not enabled');

    $exception = CouldNotFetchDns::digReturnedWithError($process, 'dig example.com');

    expect($exception->getMessage())->toContain('(no reply from server)');
});

it('keeps the dig output after the exit code as supplementary context', function () {
    $process = failedDigProcess(exitCode: 9, stderr: ';; IDN output support not enabled');

    $exception = CouldNotFetchDns::digReturnedWithError($process, 'dig example.com');

    expect($exception->getMessage())->toMatch('/exit code 9.*IDN output support not enabled/');
});

it('falls back to stdout when stderr is empty', function () {
    $process = failedDigProcess(exitCode: 1, stdout: 'some stdout error');

    $exception = CouldNotFetchDns::digReturnedWithError($process, 'dig example.com');

    expect($exception->getMessage())->toMatch('/exit code 1.*some stdout error/');
});

it('omits the trailing context when dig produced no output', function () {
    $process = failedDigProcess(exitCode: 10);

    $exception = CouldNotFetchDns::digReturnedWithError($process, 'dig example.com');

    expect($exception->getMessage())
        ->toContain('failed with exit code 10')
        ->not->toContain('``');
});

function failedDigProcess(int $exitCode, string $stderr = '', string $stdout = ''): Process
{
    $script = '';

    if ($stdout !== '') {
        $script .= 'printf %s ' . escapeshellarg($stdout) . '; ';
    }

    if ($stderr !== '') {
        $script .= 'printf %s ' . escapeshellarg($stderr) . ' >&2; ';
    }

    $script .= "exit {$exitCode}";

    $process = new Process(['sh', '-c', $script]);
    $process->run();

    return $process;
}
