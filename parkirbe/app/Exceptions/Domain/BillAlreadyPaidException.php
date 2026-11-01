<?php

declare(strict_types=1);

namespace App\Exceptions\Domain;

/**
 * Exception ketika tagihan member sudah pernah dilunaskan.
 * HTTP 409 Conflict — mencegah pembayaran ganda.
 */
class BillAlreadyPaidException extends DomainException
{
    public function __construct(int $billId)
    {
        parent::__construct("Tagihan #{$billId} sudah dilunaskan sebelumnya.");
    }

    public function getStatusCode(): int
    {
        return 409;
    }
}
