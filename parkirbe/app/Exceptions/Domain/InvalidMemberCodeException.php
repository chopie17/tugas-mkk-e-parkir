<?php

declare(strict_types=1);

namespace App\Exceptions\Domain;

/**
 * Exception ketika kode member yang di-scan tidak ditemukan di database.
 * HTTP 422.
 */
class InvalidMemberCodeException extends DomainException
{
    public function __construct(string $code)
    {
        parent::__construct("Kartu tidak valid. Kode '{$code}' tidak ditemukan.");
    }

    public function getStatusCode(): int
    {
        return 422;
    }
}
