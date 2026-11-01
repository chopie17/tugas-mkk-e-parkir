<?php

declare(strict_types=1);

namespace App\Exceptions\Domain;

use Exception;

/**
 * Base class untuk semua domain exception aplikasi parkir.
 * Exception ini akan di-render menjadi JSON response oleh exception handler.
 */
abstract class DomainException extends Exception
{
    /**
     * HTTP status code yang akan dikirim ke client.
     */
    abstract public function getStatusCode(): int;
}
