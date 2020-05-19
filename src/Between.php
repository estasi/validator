<?php

declare(strict_types=1);

namespace Estasi\Validator;

use RuntimeException;

use function sprintf;

use const PHP_INT_MAX;
use const PHP_INT_MIN;

/**
 * Class Between
 *
 * @package Estasi\Validator
 */
final class Between extends Abstracts\Validator implements Interfaces\Min, Interfaces\Max, Interfaces\Inclusive
{
    use Traits\ConvertNumericValueToFloat;

    // names of constructor parameters to create via the factory
    public const OPT_MIN_INCLUSIVE = 'minInclusive';
    public const OPT_MAX_INCLUSIVE = 'maxInclusive';

    private GreaterThan $greaterThan;
    private LessThan    $lessThan;

    /**
     * Between constructor.
     *
     * @param int|float                    $min          Minimum value of the verification range
     * @param bool                         $minInclusive Enables or disables the minimum value in the verification
     *                                                   range
     * @param int|float                    $max          Maximum value of the verification range
     * @param bool                         $maxInclusive Enables or disables the maximum value in the verification
     *                                                   range
     * @param iterable<string, mixed>|null $options      Secondary validator options, such as the Translator, the
     *                                                   length of the error message, hiding the value being checked,
     *                                                   defining your own error messages, and so on
     */
    public function __construct(
        $min = PHP_INT_MIN,
        bool $minInclusive = self::NOT_INCLUSIVE,
        $max = PHP_INT_MAX,
        bool $maxInclusive = self::NOT_INCLUSIVE,
        iterable $options = null
    ) {
        parent::__construct();

        $min = $this->convertNumericValueToFloat($min, self::OPT_MIN);
        $max = $this->convertNumericValueToFloat($max, self::OPT_MAX);
        if ($min > $max) {
            throw new RuntimeException(sprintf('Invalid comparison interval: %s > %s!', $min, $max));
        }

        $this->greaterThan = new GreaterThan($min, $minInclusive, $options);
        $this->lessThan    = new LessThan($max, $maxInclusive, $options);
    }

    /**
     * @inheritDoc
     */
    public function isValid($value, $context = null): bool
    {
        if (false === $this->greaterThan->isValid($value, $context)) {
            $this->mergeErrors($this->greaterThan->getLastError());

            return false;
        }
        if (false === $this->lessThan->isValid($value, $context)) {
            $this->mergeErrors($this->lessThan->getLastError());

            return false;
        }

        return true;
    }
}
