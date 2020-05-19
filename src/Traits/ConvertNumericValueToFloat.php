<?php

declare(strict_types=1);

namespace Estasi\Validator\Traits;

use Estasi\Utility\Traits\ReceivedTypeForException;
use InvalidArgumentException;

use function is_numeric;
use function sprintf;

/**
 * Trait checkRange
 *
 * @package Estasi\Validator\Traits
 */
trait ConvertNumericValueToFloat
{
    use ReceivedTypeForException;

    /**
     * @param mixed  $range
     * @param string $name
     *
     * @return float
     */
    private function convertNumericValueToFloat($range, string $name): float
    {
        if (is_numeric($range)) {
            return (float)$range;
        }
        throw new InvalidArgumentException(
            sprintf(
                'Expected type of argument "%s" is an int or float; received %s!',
                $name,
                $this->getReceivedType($range)
            )
        );
    }
}
