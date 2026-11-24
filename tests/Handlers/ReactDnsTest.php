<?php

use React\Dns\Model\Message;
use React\Dns\Model\Record as ResourceRecord;
use React\Dns\Query\Query;
use Spatie\Dns\Exceptions\CouldNotFetchDns;
use Spatie\Dns\Handlers\Dig;
use Spatie\Dns\Handlers\Handler;
use Spatie\Dns\Handlers\ReactDns;
use Spatie\Dns\Support\Factory;

it('returns the same records as dig', function (string $domain, string $nameserver, string $type) {
    $react = recordStrings(new ReactDns(new Factory), $domain, $nameserver, $type);
    $dig = recordStrings(new Dig(new Factory), $domain, $nameserver, $type);

    expect($react)->not->toBeEmpty()->toBe($dig);
})->with([
    ['cloudflare.com', 'ns3.cloudflare.com', 'A'],
    ['cloudflare.com', 'ns3.cloudflare.com', 'AAAA'],
    ['cloudflare.com', 'ns3.cloudflare.com', 'NS'],
    ['cloudflare.com', 'ns3.cloudflare.com', 'SOA'],
    ['cloudflare.com', 'ns3.cloudflare.com', 'MX'],
    ['cloudflare.com', 'ns3.cloudflare.com', 'TXT'],
    ['cloudflare.com', 'ns3.cloudflare.com', 'CAA'],
    ['cloudflare.com', 'ns3.cloudflare.com', 'DNSKEY'],
    ['cloudflare.com', 'a.gtld-servers.net', 'DS'],
    ['spatie.be', 'ns1.openminds.be', 'MX'],
    ['spatie.be', 'ns1.openminds.be', 'TXT'],
    ['münchen.de', 'a.nic.de', 'NS'],
]);

it('throws when a real nameserver refuses the query', function () {
    $handler = (new ReactDns(new Factory))
        ->useNameserver('ns1.digitalocean.com')
        ->throwOnErrorResponse(true);

    expect(fn () => $handler('google.com', DNS_A, 'A'))
        ->toThrow(fn (CouldNotFetchDns $exception) => expect($exception->responseStatus)->toBe('REFUSED'));
});

it('names the error status the way dig does', function (int $responseCode, string $status) {
    $handler = reactDnsAnswering(dnsMessage(responseCode: $responseCode))->throwOnErrorResponse(true);

    expect(fn () => $handler('spatie.be', DNS_A, 'A'))
        ->toThrow(fn (CouldNotFetchDns $exception) => expect($exception->responseStatus)->toBe($status));
})->with([
    [Message::RCODE_FORMAT_ERROR, 'FORMERR'],
    [Message::RCODE_SERVER_FAILURE, 'SERVFAIL'],
    [Message::RCODE_NOT_IMPLEMENTED, 'NOTIMP'],
    [Message::RCODE_REFUSED, 'REFUSED'],
    [15, 'RESERVED15'],
]);

it('returns the records of a successful answer when asked to throw', function (int $responseCode) {
    $handler = reactDnsAnswering(dnsMessage(
        responseCode: $responseCode,
        answers: [new ResourceRecord('spatie.be', Message::TYPE_A, Message::CLASS_IN, 300, '138.197.187.74')],
    ))->throwOnErrorResponse(true);

    expect($handler('spatie.be', DNS_A, 'A'))->toHaveCount(1);
})->with([Message::RCODE_OK, Message::RCODE_NAME_ERROR]);

it('returns no records for an error status when not asked to throw', function () {
    $handler = reactDnsAnswering(dnsMessage(responseCode: Message::RCODE_REFUSED));

    expect($handler('spatie.be', DNS_A, 'A'))->toBe([]);
});

it('throws without a response status when the nameserver does not reply', function () {
    $handler = (new ReactDns(new Factory))
        ->useNameserver('192.0.2.1')
        ->setTimeout(1)
        ->setRetries(2);

    $start = microtime(true);

    expect(fn () => $handler('spatie.be', DNS_A, 'A'))
        ->toThrow(fn (CouldNotFetchDns $exception) => expect($exception->responseStatus)->toBeNull());

    expect(microtime(true) - $start)->toBeGreaterThanOrEqual(2.0)->toBeLessThan(3.0);
});

it('throws when the nameserver hostname does not resolve', function () {
    $handler = (new ReactDns(new Factory))->useNameserver('nameserver.invalid');

    $handler('spatie.be', DNS_A, 'A');
})->throws(CouldNotFetchDns::class);

it('only returns records of the requested type, from the answer and the authority section', function () {
    $handler = reactDnsAnswering(dnsMessage(
        answers: [
            new ResourceRecord('www.spatie.be', Message::TYPE_CNAME, Message::CLASS_IN, 300, 'spatie.be'),
            new ResourceRecord('spatie.be', Message::TYPE_SOA, Message::CLASS_IN, 300, soaData()),
        ],
        authority: [
            new ResourceRecord('be', Message::TYPE_SOA, Message::CLASS_IN, 600, soaData()),
        ],
    ));

    $hosts = array_map(fn ($record) => $record->host(), $handler('www.spatie.be', DNS_SOA, 'SOA'));

    expect($hosts)->toBe(['spatie.be', 'be']);
});

it('turns the answer into the record that dig output gives', function (string $type, int $typeCode, mixed $data, string $digLine) {
    $handler = reactDnsAnswering(dnsMessage(
        answers: [new ResourceRecord('example.com', $typeCode, Message::CLASS_IN, 86400, $data)],
    ));

    $records = $handler('example.com', 0, $type);

    expect(array_map('strval', $records))->toBe([(string) (new Factory)->parse($type, $digLine)]);
})->with([
    'DS' => [
        'DS',
        43,
        pack('nCC', 60485, 5, 1).hex2bin('2bb183af5f22588179a53b0a98631fad1a292118'),
        'example.com. 86400 IN DS 60485 5 1 2BB183AF5F22588179A53B0A98631FAD1A292118',
    ],
    'DNSKEY' => [
        'DNSKEY',
        48,
        pack('nCC', 257, 3, 13).base64_decode('mdsswUyr3DPW132mOi8V9xESWE8jTo0dxCjjnopKl+GqJxpVXckHAeF+KkxLbxILfDLUT0rAK9iUzy1L53eKGQ=='),
        'example.com. 86400 IN DNSKEY 257 3 13 mdsswUyr3DPW132mOi8V9xESWE8jTo0d xCjjnopKl+GqJxpVXckHAeF+KkxLbxIL fDLUT0rAK9iUzy1L53eKGQ==',
    ],
    'TXT with escapes' => [
        'TXT',
        Message::TYPE_TXT,
        ["say \"hi\" \\ caf\u{e9}", "tab\there"],
        'example.com. 86400 IN TXT "say \"hi\" \\\\ caf\195\169" "tab\009here"',
    ],
    'CAA' => [
        'CAA',
        Message::TYPE_CAA,
        ['flag' => 128, 'tag' => 'issue', 'value' => 'letsencrypt.org; validationmethods=dns-01'],
        'example.com. 86400 IN CAA 128 issue "letsencrypt.org; validationmethods=dns-01"',
    ],
    'null MX' => [
        'MX',
        Message::TYPE_MX,
        ['priority' => 0, 'target' => ''],
        'example.com. 86400 IN MX 0 .',
    ],
]);

function recordStrings(Handler $handler, string $domain, string $nameserver, string $type): array
{
    $records = array_map('strval', $handler->useNameserver($nameserver)($domain, 0, $type));

    sort($records);

    return $records;
}

function reactDnsAnswering(Message $response): ReactDns
{
    $handler = new class(new Factory) extends ReactDns
    {
        public Message $response;

        protected function query(Query $query, string $nameserver, string $description): Message
        {
            return $this->response;
        }
    };

    $handler->response = $response;

    return $handler->useNameserver('127.0.0.1');
}

function dnsMessage(int $responseCode = Message::RCODE_OK, array $answers = [], array $authority = []): Message
{
    $message = new Message;
    $message->qr = true;
    $message->rcode = $responseCode;
    $message->answers = $answers;
    $message->authority = $authority;

    return $message;
}

function soaData(): array
{
    return [
        'mname' => 'ns1.example.com',
        'rname' => 'hostmaster.example.com',
        'serial' => 2026112401,
        'refresh' => 10800,
        'retry' => 3600,
        'expire' => 604800,
        'minimum' => 3600,
    ];
}
