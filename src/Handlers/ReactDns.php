<?php

namespace Spatie\Dns\Handlers;

use React\Dns\Config\Config;
use React\Dns\Model\Message;
use React\Dns\Model\Record as ResourceRecord;
use React\Dns\Query\Query;
use React\Dns\Query\RetryExecutor;
use React\Dns\Query\SelectiveTransportExecutor;
use React\Dns\Query\TcpTransportExecutor;
use React\Dns\Query\TimeoutException;
use React\Dns\Query\TimeoutExecutor;
use React\Dns\Query\UdpTransportExecutor;
use React\EventLoop\StreamSelectLoop;
use Spatie\Dns\Exceptions\CouldNotFetchDns;
use Throwable;

/**
 * Queries the nameserver from inside PHP with react/dns, so no dig process is started.
 * Each answer is written out as the line dig would print and parsed the same way, so the
 * records are equal to the ones the Dig handler returns.
 */
class ReactDns extends Handler
{
    protected ?int $timeout = 2;

    protected ?int $retries = 2;

    /** @var array<string, int> */
    protected array $typeCodes = [
        'A' => 1,
        'NS' => 2,
        'CNAME' => 5,
        'SOA' => 6,
        'PTR' => 12,
        'MX' => 15,
        'TXT' => 16,
        'AAAA' => 28,
        'SRV' => 33,
        'DS' => 43,
        'DNSKEY' => 48,
        'CAA' => 257,
    ];

    /**
     * The names dig prints for each response code. Codes 11 to 15 have no name, and dig
     * prints them as RESERVED11 to RESERVED15.
     *
     * @var array<int, string>
     */
    protected array $responseStatuses = [
        0 => 'NOERROR',
        1 => 'FORMERR',
        2 => 'SERVFAIL',
        3 => 'NXDOMAIN',
        4 => 'NOTIMP',
        5 => 'REFUSED',
        6 => 'YXDOMAIN',
        7 => 'YXRRSET',
        8 => 'NXRRSET',
        9 => 'NOTAUTH',
        10 => 'NOTZONE',
    ];

    /**
     * Response statuses that are real answers. NXDOMAIN means the name does not exist, so
     * returning no records is correct.
     *
     * @var array<int, string>
     */
    protected array $successfulResponseStatuses = ['NOERROR', 'NXDOMAIN'];

    /**
     * The IP address for each nameserver that was given as a hostname, so the hostname is
     * only looked up once. The empty key holds the system nameserver.
     *
     * @var array<string, string>
     */
    protected array $nameserverAddresses = [];

    public function __invoke(string $domain, int $flag, string $type): array
    {
        $type = mb_strtoupper($type);

        if (! isset($this->typeCodes[$type])) {
            throw UnsupportedRecordType::make($type);
        }

        $nameserver = $this->nameserverAddress();

        $description = "{$domain} {$type} @{$nameserver}";

        $domain = $this->toAscii($domain, $description);

        $response = $this->query(
            new Query($domain, $this->typeCodes[$type], Message::CLASS_IN),
            $nameserver,
            $description,
        );

        $this->guardAgainstErrorResponse($response, $description);

        return $this->transform($type, $this->toLines($response, $type));
    }

    public function canHandle(): bool
    {
        return class_exists(UdpTransportExecutor::class);
    }

    /**
     * Runs a private event loop until the query has an answer or has failed. A private loop
     * cannot start or stop timers that the application put on the global loop.
     */
    protected function query(Query $query, string $nameserver, string $description): Message
    {
        $loop = new StreamSelectLoop;

        $executor = new RetryExecutor(
            new TimeoutExecutor(
                new SelectiveTransportExecutor(
                    new UdpTransportExecutor($nameserver, $loop),
                    new TcpTransportExecutor($nameserver, $loop),
                ),
                max(1, (int) $this->timeout),
                $loop,
            ),
            max(1, (int) $this->retries) - 1,
        );

        $settled = false;
        $response = null;
        $error = null;

        $executor->query($query)->then(
            function (Message $message) use (&$settled, &$response, $loop) {
                $settled = true;
                $response = $message;
                $loop->stop();
            },
            function (Throwable $exception) use (&$settled, &$error, $loop) {
                $settled = true;
                $error = $exception;
                $loop->stop();
            },
        );

        if (! $settled) {
            $loop->run();
        }

        if ($error !== null) {
            throw CouldNotFetchDns::queryFailed($description, $this->failureReason($error), $error);
        }

        if ($response === null) {
            throw CouldNotFetchDns::queryFailed($description, 'the query never finished');
        }

        return $response;
    }

    /**
     * Converts a Unicode domain name to its punycode form, the way dig does before it sends
     * the query. react/dns would send the UTF-8 bytes as they are.
     */
    protected function toAscii(string $domain, string $description): string
    {
        if (mb_check_encoding($domain, 'ASCII')) {
            return $domain;
        }

        if (! function_exists('idn_to_ascii')) {
            throw CouldNotFetchDns::queryFailed($description, 'a Unicode domain name needs the intl extension');
        }

        $ascii = idn_to_ascii($domain, IDNA_NONTRANSITIONAL_TO_ASCII, INTL_IDNA_VARIANT_UTS46);

        if ($ascii === false) {
            throw CouldNotFetchDns::queryFailed($description, 'the domain name cannot be converted to punycode');
        }

        return $ascii;
    }

    /**
     * The retry executor wraps the last timeout in an exception of its own, whose message
     * repeats the query. The timeout is the actual reason.
     */
    protected function failureReason(Throwable $error): string
    {
        if ($error instanceof TimeoutException) {
            return 'no reply from the nameserver';
        }

        if ($error->getPrevious() instanceof TimeoutException) {
            return 'no reply from the nameserver';
        }

        return $error->getMessage();
    }

    /**
     * Returns the IP address to send the query to. dig accepts a hostname, but react/dns
     * needs an IP address.
     */
    protected function nameserverAddress(): string
    {
        $nameserver = (string) $this->nameserver;

        if (isset($this->nameserverAddresses[$nameserver])) {
            return $this->nameserverAddresses[$nameserver];
        }

        return $this->nameserverAddresses[$nameserver] = $this->resolveNameserver($nameserver);
    }

    protected function resolveNameserver(string $nameserver): string
    {
        if ($nameserver === '') {
            return Config::loadSystemConfigBlocking()->nameservers[0]
                ?? throw CouldNotFetchDns::queryFailed('system nameserver', 'no nameserver is configured on this system');
        }

        if (filter_var($nameserver, FILTER_VALIDATE_IP) !== false) {
            return $nameserver;
        }

        $address = gethostbyname($nameserver);

        if (filter_var($address, FILTER_VALIDATE_IP) === false) {
            throw CouldNotFetchDns::queryFailed("nameserver {$nameserver}", 'its IP address could not be found');
        }

        return $address;
    }

    protected function guardAgainstErrorResponse(Message $response, string $description): void
    {
        if (! $this->throwOnErrorResponse) {
            return;
        }

        $responseStatus = $this->responseStatuses[$response->rcode] ?? "RESERVED{$response->rcode}";

        if (in_array($responseStatus, $this->successfulResponseStatuses, true)) {
            return;
        }

        throw CouldNotFetchDns::nameserverReturnedErrorResponse($responseStatus, $description);
    }

    /**
     * Writes each record of the requested type as the line dig prints for it. The Dig
     * handler reads both the answer and the authority section, so this does too.
     *
     * @return array<int, string>
     */
    protected function toLines(Message $response, string $type): array
    {
        $lines = [];

        foreach ([...$response->answers, ...$response->authority] as $record) {
            if ($record->type !== $this->typeCodes[$type]) {
                continue;
            }

            $data = $this->formatData($record, $type);

            if ($data === null) {
                continue;
            }

            $lines[] = "{$this->formatName($record->name)}\t{$record->ttl}\tIN\t{$type}\t{$data}";
        }

        return $lines;
    }

    protected function formatData(ResourceRecord $record, string $type): ?string
    {
        $data = $record->data;

        return match ($type) {
            'A', 'AAAA' => $data,
            'CNAME', 'NS', 'PTR' => $this->formatName($data),
            'MX' => "{$data['priority']} {$this->formatName($data['target'])}",
            'SRV' => "{$data['priority']} {$data['weight']} {$data['port']} {$this->formatName($data['target'])}",
            'SOA' => implode(' ', [
                $this->formatName($data['mname']),
                $this->formatName($data['rname']),
                $data['serial'],
                $data['refresh'],
                $data['retry'],
                $data['expire'],
                $data['minimum'],
            ]),
            'TXT' => implode(' ', array_map($this->formatCharacterString(...), $data)),
            'CAA' => "{$data['flag']} {$data['tag']} {$this->formatCharacterString($data['value'])}",
            'DS' => $this->formatDs($data),
            'DNSKEY' => $this->formatDnskey($data),
        };
    }

    /**
     * react/dns leaves the trailing dot of the root label off, dig prints it.
     */
    protected function formatName(string $name): string
    {
        return rtrim($name, '.').'.';
    }

    /**
     * Quotes the string the way dig does. A quote or a backslash gets a backslash in front,
     * and a byte that is not printable ASCII is written as a backslash and three digits.
     */
    protected function formatCharacterString(string $value): string
    {
        $formatted = '';

        foreach (str_split($value) as $character) {
            $byte = ord($character);

            if (in_array($character, ['"', '\\'], true)) {
                $formatted .= "\\{$character}";

                continue;
            }

            if ($byte < 32 || $byte > 126) {
                $formatted .= sprintf('\\%03d', $byte);

                continue;
            }

            $formatted .= $character;
        }

        return "\"{$formatted}\"";
    }

    /**
     * react/dns does not decode DS records and returns the raw bytes. They hold a 2 byte key
     * tag, a 1 byte algorithm, a 1 byte digest type and the digest.
     */
    protected function formatDs(string $data): ?string
    {
        if (strlen($data) < 5) {
            return null;
        }

        $fields = unpack('nkeytag/Calgorithm/Cdigesttype', $data);

        $digest = strtoupper(bin2hex(substr($data, 4)));

        return "{$fields['keytag']} {$fields['algorithm']} {$fields['digesttype']} {$digest}";
    }

    /**
     * react/dns does not decode DNSKEY records and returns the raw bytes. They hold 2 bytes
     * of flags, a 1 byte protocol, a 1 byte algorithm and the public key.
     */
    protected function formatDnskey(string $data): ?string
    {
        if (strlen($data) < 5) {
            return null;
        }

        $fields = unpack('nflags/Cprotocol/Calgorithm', $data);

        $publicKey = base64_encode(substr($data, 4));

        return "{$fields['flags']} {$fields['protocol']} {$fields['algorithm']} {$publicKey}";
    }
}
