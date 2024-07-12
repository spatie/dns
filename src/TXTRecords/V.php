<?php
namespace Spatie\Dns\TXTRecords;

use ReflectionClass;
use Spatie\Dns\Exceptions\InvalidArgument;
use Spatie\Macroable\Macroable;

abstract class V {
    use Macroable {
        __call as protected macroCall;
    }

    protected string $type;
    protected string $version;

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
                $return = $this->cast($key, $value);
                if(is_array($return)){
                    foreach($return as $key => $subvalue){
                        $this->$key = $subvalue;
                    }
                } else {
                    $this->$key = $return;
                }
            }
        }
    }
    
    public function __call(string $name, array $arguments)
    {
        if (property_exists($this, $name)) {
            return $this->$name;
        }

        return $this->macroCall($name, $arguments);
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

    protected function cast(string $attribute, $value)
    {
        $method = sprintf('cast%s', str_replace(' ', '', ucwords(str_replace('_', ' ', $attribute))));

        if (method_exists($this, $method)) {
            return $this->$method($value);
        } else {
            return $value;
        }

        return $value;
    }
}