<?php
namespace App\Support;

use InvalidArgumentException;

class Validator
{
    public static function require(array $data, array $required): void
    {
        $missing = [];
        foreach ($required as $field) {
            if (!array_key_exists($field, $data) || $data[$field] === '' || $data[$field] === null) {
                $missing[] = $field;
            }
        }

        if (!empty($missing)) {
            throw new InvalidArgumentException('Missing required fields: ' . implode(', ', $missing));
        }
    }
}
