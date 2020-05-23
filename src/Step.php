<?php

declare(strict_types=1);

namespace Estasi\Validator;

use Estasi\Utility\Interfaces\VariableType;
use RangeException;

use function abs;
use function compact;
use function fmod;
use function is_numeric;

/**
 * Class Step
 *
 * @property-read float $step
 * @property-read float $startingPoint
 * @package Estasi\Validator
 */
final class Step extends Abstracts\Validator
{
    use Traits\ConvertNumericValueToFloat;

    // names of constructor parameters to create via the factory
    public const OPT_STEP           = 'step';
    public const OPT_STARTING_POINT = 'startingPoint';
    // default values for constructor parameters
    public const DEFAULT_STEP_ONE        = 1.0;
    public const ZERO_POINT_OF_REFERENCE = 0.0;
    // error code
    public const E_NOT_STEP = 'eNotStep';

    /**
     * Step constructor.
     *
     * @param int|float                    $step          Step of checking the value from the reference point
     * @param int|float                    $startingPoint The reference point from which the check is performed
     * @param iterable<string, mixed>|null $options       Secondary validator options, such as the Translator, the
     *                                                    length of the error message, hiding the value being checked,
     *                                                    defining your own error messages, and so on.
     *
     * @throws \RangeException An exception is thrown if the check step is zero
     */
    public function __construct(
        $step = self::DEFAULT_STEP_ONE,
        $startingPoint = self::ZERO_POINT_OF_REFERENCE,
        iterable $options = null
    )
    {
        $step          = $this->convertNumericValueToFloat($step, self::OPT_STEP);
        $startingPoint = $this->convertNumericValueToFloat($startingPoint, self::OPT_STARTING_POINT);
        if (0.0 === $step) {
            throw new RangeException('Step must not be zero!');
        }

        parent::__construct(...$this->createProperties($options, compact('step', 'startingPoint')));
        $this->initErrorMessagesTemplates(
            [self::E_NOT_STEP => 'The checked value "%value%" does not match the step "%step%" from the initial value "%startingPoint%"!']
        );
        $this->initErrorMessagesVars(
            [
                self::OPT_STEP                   => $this->step,
                self::OPT_STARTING_POINT         => $this->startingPoint,
                self::MESSAGE_VAR_TYPES_EXPECTED => [VariableType::INTEGER, VariableType::DOUBLE],
            ]
        );
    }

    /**
     * @param int|float $value
     *
     * @param null      $context
     *
     * @return bool
     */
    public function isValid($value, $context = null): bool
    {
        if (false === is_numeric($value)) {
            $this->error(self::E_INVALID_TYPE);

            return false;
        }

        $x = $value - $this->startingPoint;
        if (abs(fmod($x, $this->step)) > 0) {
            $this->error(self::E_NOT_STEP, [self::MESSAGE_VAR_VALUE => $value]);

            return false;
        }

        return true;
    }
}
