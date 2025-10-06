<?php
namespace App\Exceptions;

use RuntimeException;

class SupabaseException extends RuntimeException
{
    private array $details;

    public function __construct(string $message, int $code = 0, array $details = [])
    {
        parent::__construct($message, $code);
        $this->details = $details;
    }

    public function details(): array
    {
        return $this->details;
    }
}
