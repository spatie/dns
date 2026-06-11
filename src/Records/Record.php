<?php

namespace Spatie\Dns\Records;

use ReflectionClass;
use Spatie\Dns\Exceptions\InvalidArgument;
use Spatie\Dns\Support\Domain;
use Spatie\Macroable\Macroable;
use Stringable;

/**
 * @method string host()
 * @method string ttl()
 * @method string class()
 * @method string type()
 */
abstract class Record implements Stringable
{
    use Macroable {
        __call as protected macroCall;
    }

    protected string $host;

    protected int $ttl;

    protected string $class;

    protected string $type;

    public function __construct(array $attributes)
    {
        $type = $attributes['type'] ?? null;
        $expectedType = (new ReflectionClass($this))->getShortName();

        if ($type !== $expectedType) {
            throw InvalidArgument::wrongRecordType($type, $expectedType);
        }

        foreach ($attributes as $key => $value) {
            $key = str_replace('-', '_', $key);

            if (property_exists($this, $key)) {
                $this->$key = $this->cast($key, $value);
            }
        }
    }

    /**
     * @return static
     */
    public static function make(array $record): self
    {
        return new static($record);
    }

    /**
     * @return static
     */
    abstract public static function parse(string $line): ?self;

    abstract public function __toString(): string;

    /**
     * @return array
     */
    abstract public function toArray();

    public function __call(string $name, array $arguments)
    {
        if (property_exists($this, $name)) {
            return $this->$name;
        }

        return $this->macroCall($name, $arguments);
    }

    protected static function lineToArray(string $line, ?int $limit = null): array
    {
        // Match non-space characters, escaped quotes within quotes, or characters within quotes
        preg_match_all('/(?:\\\\["]|[^"\\s]+|"[^"\\\\]*(?:\\\\.[^"\\\\]*)*")+/u', $line, $matches);
        $parts = $matches[0];

        // If a limit is defined, handle it manually because preg_match_all doesn't support limit
        if ($limit !== null && count($parts) > $limit) {
            $lastPart = implode(' ', array_slice($parts, $limit - 1));
            $parts = array_slice($parts, 0, $limit - 1);
            $parts[] = $lastPart;
        }

        return $parts;
    }

    protected function cast(string $attribute, $value)
    {
        $method = sprintf('cast%s', str_replace(' ', '', ucwords(str_replace('_', ' ', $attribute))));

        if (method_exists($this, $method)) {
            return $this->$method($value);
        }

        return $value;
    }

    protected function prepareDomain(string $value): string
    {
        // The root label "." is a valid target for a null MX (RFC 7505) and for an
        // SRV record that declines a service (RFC 2782). Stored dot-stripped like every
        // other domain, the root becomes an empty string and __toString re-appends the dot.
        if (trim($value, '.') === '') {
            return '';
        }

        return strval(new Domain(trim($value, '.')));
    }

    protected function prepareInt($value): int
    {
        return intval($value);
    }

    protected function prepareText(string $value): string
    {
        if (str_starts_with($value, '"') && str_ends_with($value, '"')) {
            $value = substr($value, 1, -1);
        }

        return str_replace('" "', '', $value);
    }

    protected function castHost(string $value): string
    {
        return $this->prepareDomain($value);
    }

    protected function castTtl($value): int
    {
        return $this->prepareInt($value);
    }

    protected function castClass(string $value): string
    {
        return mb_strtoupper($value);
    }

    protected function castType(string $value): string
    {
        return mb_strtoupper($value);
    }
}
