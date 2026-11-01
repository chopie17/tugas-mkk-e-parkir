<?php

declare(strict_types=1);

namespace App\Enums;

/**
 * Enum untuk hasil scan kartu member.
 */
enum EntryResult: string
{
    case Allowed = 'allowed';
    case Denied  = 'denied';
}
