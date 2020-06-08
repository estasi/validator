<?php

declare(strict_types=1);

namespace Estasi\Validator;

use function compact;

use const PHP_INT_MAX;

/**
 * Class LessThan
 *
 * @property-read float $max
 * @property-read bool  $inclusive
 * @package Estasi\Validator
 */
final class LessThan extends Abstracts\Validator implements Interfaces\Max, Interfaces\Inclusive
{
    use Traits\ConvertNumericValueToFloat;
    use Traits\Countable;

    // errors code
    public const E_NOT_LESS_THAN           = 'eNotLessThan';
    public const E_NOT_LESS_THAN_INCLUSIVE = 'eNotLessThanInclusive';

    private IsCountable $isCountable;

    /**
     * LessThan constructor.
     *
     * @param int|float                    $max       Maximum value of the verification range
     * @param bool                         $inclusive Enables or disables the maximum value in the verification range
     * @param iterable<string, mixed>|null $options   Secondary validator options, such as the Translator, the length
     *                                                of the error message, hiding the value being checked, defining
     *                                                your own error messages, and so on.
     *
     * @throws \InvalidArgumentException
     */
    public function __construct($max = PHP_INT_MAX, bool $inclusive = self::NOT_INCLUSIVE, iterable $options = null)
    {
        $max = $this->convertNumericValueToFloat($max, self::OPT_MAX);
        parent::__construct(...$this->createProperties($options, compact('max', 'inclusive')));
        $this->initErrorMessagesTemplates(
            [
                self::E_NOT_LESS_THAN           => 'The checked value "%value%" is greater than or equal to the maximum value "%max%"!',
                self::E_NOT_LESS_THAN_INCLUSIVE => 'The checked value "%value%" is greater than the maximum value "%max%"!',
            ]
        );
        $this->initErrorMessagesVars([self::OPT_MAX => $max]);
        $this->isCountable = new IsCountable($options);
    }


    /**
     * @inheritDoc
     */
    public function isValid($value, $context = null): bool
    {
        if (false === $this->isCountable->isValid($value, $context)) {
            $this->setErrors($this->isCountable->getLastErrors());

            return false;
        }

        $this->setValue($value);
        $quantity = $this->count($value);

        return $this->inclusive
            ? $this->lessThanInclusive($quantity)
            : $this->lessThan($quantity);
    }

    protected function lessThanInclusive(float $value): bool
    {
        if ($value > $this->max) {
            $this->error(self::E_NOT_LESS_THAN_INCLUSIVE);

            return false;
        }

        return true;
    }

    protected function lessThan(float $value): bool
    {
        if ($value >= $this->max) {
            $this->error(self::E_NOT_LESS_THAN);

            return false;
        }

        return true;
    }
}
