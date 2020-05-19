<?php

declare(strict_types=1);

namespace Estasi\Validator\Traits;

use function array_chunk;
use function array_pad;
use function implode;
use function is_null;
use function sprintf;

/**
 * Trait ConvertArrayToDateString
 *
 * @package Estasi\Validator\Traits
 */
trait ConvertArrayToDateString
{
    private function convertArrayToDateString(array $value): string
    {
        [$date, $time] = array_chunk($value, 3);
        if (is_null($date)) {
            $date = [1970];
        }
        if (is_null($time)) {
            $time = [];
        }
        $date = array_pad($date, 3, 1);
        $time = array_pad($time, 3, 0);

        return sprintf("%s %s", implode('-', $date), implode(':', $time));
    }
}
