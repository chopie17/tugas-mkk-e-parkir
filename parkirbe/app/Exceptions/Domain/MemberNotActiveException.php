<?php

declare(strict_types=1);

namespace App\Exceptions\Domain;

/**
 * Exception ketika member yang di-scan statusnya tidak aktif.
 * HTTP 422.
 */
class MemberNotActiveException extends DomainException
{
    public function __construct()
    {
        parent::__construct("Kartu tidak valid. Member tidak aktif.");
    }

    public function getStatusCode(): int
    {
        return 422;
    }
}
