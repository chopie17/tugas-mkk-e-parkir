<?php

declare(strict_types=1);

namespace App\Exceptions\Domain;

/**
 * Exception ketika member scan kartu tetapi ada tagihan yang belum lunas.
 * HTTP 422.
 */
class UnpaidBillException extends DomainException
{
    public function __construct()
    {
        parent::__construct("Tagihan belum lunas, silakan bayar ke petugas.");
    }

    public function getStatusCode(): int
    {
        return 422;
    }
}
