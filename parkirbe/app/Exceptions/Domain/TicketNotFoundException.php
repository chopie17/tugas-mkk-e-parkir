<?php

declare(strict_types=1);

namespace App\Exceptions\Domain;

/**
 * Exception ketika kode tiket parkir tidak ditemukan di database.
 * HTTP 404.
 */
class TicketNotFoundException extends DomainException
{
    public function __construct(string $token)
    {
        parent::__construct("Tiket parkir dengan kode '{$token}' tidak ditemukan dalam sistem.");
    }

    public function getStatusCode(): int
    {
        return 404;
    }
}

