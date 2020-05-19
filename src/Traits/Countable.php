<?php

declare(strict_types=1);

namespace Estasi\Validator\Traits;

use Estasi\Utility\{
    ArrayUtils,
    Interfaces\VariableType
};
use Traversable;

use function count;
use function gettype;
use function mb_strlen;

/**
 * Trait Length
 *
 * @package Estasi\Validator\Traits
 */
trait Countable
{
    /**
     * Returns the length of the received value
     * Returns 0 if it is not possible to count the number of elements
     *
     * @param mixed $value
     *
     * @return float
     */
    protected function count($value): float
    {
        switch (gettype($value)) {
            case VariableType::INTEGER:
            case VariableType::DOUBLE:
                return (float)$value;
            case VariableType::STRING:
                return (float)mb_strlen($value);
            case VariableType::ARRAY:
                return (float)count($value);
            case VariableType::OBJECT:
                if ($value instanceof \Countable) {
                    return (float)$value->count();
                }
                if ($value instanceof Traversable) {
                    return (float)count(ArrayUtils::iteratorToArray($value));
                }

                return 0.0;
            default:
                return 0.0;
        }
    }
}
