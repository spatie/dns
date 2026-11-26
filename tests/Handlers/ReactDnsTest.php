<?php

use React\Dns\Model\Message;
use React\Dns\Model\Record as ResourceRecord;
use React\Dns\Query\Query;
use Spatie\Dns\Dns;
use Spatie\Dns\Exceptions\CouldNotFetchDns;
use Spatie\Dns\Handlers\Dig;
use Spatie\Dns\Handlers\Handler;
use Spatie\Dns\Handlers\ReactDns;
use Spatie\Dns\Support\Factory;
use Spatie\Dns\Test\TestClasses\CustomHandler;

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
    ['cloudflare.com.', 'ns3.cloudflare.com', 'MX'],
    ['münchen.de', 'a.nic.de', 'NS'],
]);

it('closes the connection of an answer that was too large for UDP', function () {
    $dns = Dns::query()->useHandlers([new ReactDns(new Factory)])->useNameserver('ns3.cloudflare.com');

    $openStreams = count(get_resources('stream'));

    $dns->getRecords('cloudflare.com', DNS_TXT);

    expect(get_resources('stream'))->toHaveCount($openStreams);
});

it('throws when a real nameserver refuses the query', function () {
    $dns = Dns::query()
        ->useHandlers([new ReactDns(new Factory)])
        ->useNameserver('ns1.digitalocean.com')
        ->throwOnErrorResponse();

    expect(fn () => $dns->getRecords('google.com', DNS_A))
        ->toThrow(fn (CouldNotFetchDns $exception) => expect($exception->responseStatus)->toBe('REFUSED'));
});

it('names the error status the way dig does', function (int $responseCode, string $status) {
    $dns = reactDnsAnswering(dnsMessage(responseCode: $responseCode))->throwOnErrorResponse();

    expect(fn () => $dns->getRecords('spatie.be', DNS_A))
        ->toThrow(fn (CouldNotFetchDns $exception) => expect($exception->responseStatus)->toBe($status));
})->with([
    [Message::RCODE_REFUSED, 'REFUSED'],
    [15, 'RESERVED15'],
]);

it('returns the records of a successful answer when asked to throw', function (int $responseCode) {
    $dns = reactDnsAnswering(dnsMessage(
        responseCode: $responseCode,
        answers: [new ResourceRecord('spatie.be', Message::TYPE_A, Message::CLASS_IN, 300, '138.197.187.74')],
    ))->throwOnErrorResponse();

    expect($dns->getRecords('spatie.be', DNS_A))->toHaveCount(1);
})->with([Message::RCODE_OK, Message::RCODE_NAME_ERROR]);

it('returns no records for an error status when not asked to throw', function () {
    $dns = reactDnsAnswering(dnsMessage(responseCode: Message::RCODE_REFUSED));

    expect($dns->getRecords('spatie.be', DNS_A))->toBe([]);
});

it('throws without a response status after the configured number of tries', function () {
    $dns = Dns::query()
        ->useHandlers([new ReactDns(new Factory)])
        ->useNameserver('192.0.2.1')
        ->setTimeout(1)
        ->setRetries(2);

    $start = microtime(true);

    expect(fn () => $dns->getRecords('spatie.be', DNS_A))
        ->toThrow(fn (CouldNotFetchDns $exception) => expect($exception->responseStatus)->toBeNull());

    expect(microtime(true) - $start)->toBeGreaterThanOrEqual(2.0)->toBeLessThan(3.0);
});

it('does not fall back to the next handler when a query fails', function () {
    $dns = Dns::query()
        ->useHandlers([new ReactDns(new Factory), new CustomHandler])
        ->useNameserver('192.0.2.1')
        ->setTimeout(1)
        ->setRetries(1);

    expect(fn () => $dns->getRecords('spatie.be', DNS_A))->toThrow(CouldNotFetchDns::class);
});

it('asks the next system nameserver when the first one does not reply', function () {
    $handler = new class(new Factory) extends ReactDns
    {
        protected function systemNameservers(): array
        {
            return ['192.0.2.1', '1.1.1.1'];
        }
    };

    $records = Dns::query()->useHandlers([$handler])->setTimeout(1)->setRetries(1)->getRecords('cloudflare.com', DNS_A);

    expect($records)->not->toBeEmpty();
});

it('spends the extra tries on the first nameserver and asks the others once', function () {
    $handler = new class(new Factory) extends ReactDns
    {
        protected function systemNameservers(): array
        {
            return ['192.0.2.1', '192.0.2.2'];
        }
    };

    $start = microtime(true);

    $dns = Dns::query()->useHandlers([$handler])->setTimeout(1)->setRetries(2);

    expect(fn () => $dns->getRecords('spatie.be', DNS_A))->toThrow(CouldNotFetchDns::class);

    expect(microtime(true) - $start)->toBeGreaterThanOrEqual(3.0)->toBeLessThan(4.0);
});

it('throws when the nameserver hostname does not resolve', function () {
    Dns::query()
        ->useHandlers([new ReactDns(new Factory)])
        ->useNameserver('nameserver.invalid')
        ->getRecords('spatie.be', DNS_A);
})->throws(CouldNotFetchDns::class);

it('only returns records of the requested type, from the answer and the authority section', function () {
    $soa = [
        'mname' => 'ns1.example.com',
        'rname' => 'hostmaster.example.com',
        'serial' => 2026112401,
        'refresh' => 10800,
        'retry' => 3600,
        'expire' => 604800,
        'minimum' => 3600,
    ];

    $dns = reactDnsAnswering(dnsMessage(
        answers: [
            new ResourceRecord('www.spatie.be', Message::TYPE_CNAME, Message::CLASS_IN, 300, 'spatie.be'),
            new ResourceRecord('spatie.be', Message::TYPE_SOA, Message::CLASS_IN, 300, $soa),
        ],
        authority: [
            new ResourceRecord('be', Message::TYPE_SOA, Message::CLASS_IN, 600, $soa),
        ],
    ));

    $hosts = array_map(fn ($record) => $record->host(), $dns->getRecords('www.spatie.be', DNS_SOA));

    expect($hosts)->toBe(['spatie.be', 'be']);
});

it('turns the answer into the record that dig output gives', function (string $type, int $typeCode, mixed $data, string $digLine) {
    $dns = reactDnsAnswering(dnsMessage(
        answers: [new ResourceRecord('example.com', $typeCode, Message::CLASS_IN, 86400, $data)],
    ));

    $records = $dns->getRecords('example.com', $type);

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
    'null MX' => [
        'MX',
        Message::TYPE_MX,
        ['priority' => 0, 'target' => ''],
        'example.com. 86400 IN MX 0 .',
    ],
]);

function recordStrings(Handler $handler, string $domain, string $nameserver, string $type): array
{
    $records = array_map('strval', Dns::query()
        ->useHandlers([$handler])
        ->useNameserver($nameserver)
        ->getRecords($domain, $type));

    sort($records);

    return $records;
}

function reactDnsAnswering(Message $response): Dns
{
    $handler = new class(new Factory) extends ReactDns
    {
        public Message $response;

        protected function queryNameserver(Query $query, string $nameserver, int $tries): Message
        {
            return $this->response;
        }
    };

    $handler->response = $response;

    return Dns::query()->useHandlers([$handler])->useNameserver('127.0.0.1');
}

function dnsMessage(int $responseCode = Message::RCODE_OK, array $answers = [], array $authority = []): Message
{
    $message = new Message;
    $message->rcode = $responseCode;
    $message->answers = $answers;
    $message->authority = $authority;

    return $message;
}
