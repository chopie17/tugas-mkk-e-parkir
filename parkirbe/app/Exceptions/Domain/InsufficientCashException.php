<?php

declare(strict_types=1);

namespace App\Exceptions\Domain;

/**
 * Exception ketika uang tunai yang diberikan kurang dari total tagihan.
 * HTTP 422 Unprocessable Entity.
 */
class InsufficientCashException extends DomainException
{
    public function __construct(int $required, int $given)
    {
        parent::__construct(
            "Uang tunai tidak cukup. Tagihan: Rp " . number_format($required, 0, ',', '.') .
            ", Diterima: Rp " . number_format($given, 0, ',', '.')
        );
    }

    public function getStatusCode(): int
    {
        return 422;
    }
}
