<?php

declare(strict_types=1);

namespace Estasi\Validator;

use const PHP_INT_MIN;

/**
 * Class GreaterThan
 *
 * @package Estasi\Validator
 */
final class GreaterThan extends Abstracts\Validator implements Interfaces\Min, Interfaces\Inclusive
{
    use Traits\ConvertNumericValueToFloat;
    use Traits\Countable;

    // errors code
    public const E_NOT_GREATER_THAN           = 'eNotGreaterThan';
    public const E_NOT_GREATER_THAN_INCLUSIVE = 'eNotGreaterThanInclusive';

    private float       $min;
    private bool        $inclusive;
    private IsCountable $isCountable;

    /**
     * GreaterThan constructor.
     *
     * @param int|float                    $min       Minimum value of the verification range
     * @param bool                         $inclusive Enables or disables the minimum value in the verification range
     * @param iterable<string, mixed>|null $options   Secondary validator options, such as the Translator, the length
     *                                                of the error message, hiding the value being checked, defining
     *                                                your own error messages, and so on.
     *
     * @throws \InvalidArgumentException
     */
    public function __construct($min = PHP_INT_MIN, bool $inclusive = self::NOT_INCLUSIVE, iterable $options = null)
    {
        $this->min         = $this->convertNumericValueToFloat($min, self::OPT_MIN);
        $this->inclusive   = $inclusive;
        $this->isCountable = new IsCountable($options);
        parent::__construct(...$this->getValidOptionsForParent($options));
        $this->initErrorMessagesTemplates(
            [
                self::E_NOT_GREATER_THAN           => 'The checked value "%value%" is less than the minimum allowed value "%min%"!',
                self::E_NOT_GREATER_THAN_INCLUSIVE => 'The checked value "%value%" is less than or equal to the minimum allowed value "%min%"!',
            ]
        );
        $this->initErrorMessagesVars([self::OPT_MIN => $this->min]);
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
            ? $this->greaterThanInclusive($quantity)
            : $this->greaterThan($quantity);
    }

    protected function greaterThanInclusive(float $value): bool
    {
        if ($this->min > $value) {
            $this->error(self::E_NOT_GREATER_THAN_INCLUSIVE);

            return false;
        }

        return true;
    }

    protected function greaterThan(float $value): bool
    {
        if ($this->min >= $value) {
            $this->error(self::E_NOT_GREATER_THAN);

            return false;
        }

        return true;
    }
}
