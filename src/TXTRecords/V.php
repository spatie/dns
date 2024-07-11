<?php

namespace Spatie\Dns\TXTRecords;

class V {
    public string $type;
    public string $version;

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