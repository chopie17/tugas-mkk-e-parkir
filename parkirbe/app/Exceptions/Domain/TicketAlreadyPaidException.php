<?php

declare(strict_types=1);

namespace App\Exceptions\Domain;

/**
 * Exception ketika tiket parkir sudah pernah dibayar sebelumnya.
 * HTTP 409 Conflict — mencegah pembayaran ganda.
 */
class TicketAlreadyPaidException extends DomainException
{
    public function __construct(string $token)
    {
        parent::__construct("Tiket dengan token '{$token}' sudah dibayar.");
    }

    public function getStatusCode(): int
    {
        return 409;
    }
}
