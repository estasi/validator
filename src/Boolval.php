<?php

declare(strict_types=1);

namespace Estasi\Validator;

use Countable;
use Estasi\Utility\{
    ArrayUtils,
    Interfaces\VariableType,
    Traits\Flags
};

use function gettype;
use function in_array;
use function is_iterable;
use function is_numeric;
use function is_object;
use function is_string;
use function method_exists;
use function preg_replace;

/**
 * Class Boolval
 *
 * @package Estasi\Validator
 */
final class Boolval extends Abstracts\Validator
{
    use Flags;

    // names of constructor parameters to create via the factory
    public const OPT_FLAGS = 'flags';
    // values for constructor parameters
    /**
     * If the object contains the __toString() method, the check is performed as with a string, otherwise it returns
     * false
     *
     * @var int 1
     */
    public const OBJECT_AS_STRING = 0b0001;
    /**
     * Returns true if the object can count the number of elements-implements the \Countable interface, otherwise
     * returns false
     *
     * @var int 2
     */
    public const OBJECT_AS_COUNTABLE = 0b0010;
    /**
     * Allows 0 for number (string, int, float)
     *
     * @var int 4
     */
    public const ALLOW_ZERO = 0b0100;
    /**
     * Disables a string consisting only of spaces
     *
     * @var int 8
     */
    public const DISALLOW_STR_CONTAINS_ONLY_SPACE = 0b1000;
    /** @var int 0 */
    public const WITHOUT_ADDITIONAL_VERIFICATION_PARAMETERS = 0;
    /** @var int 15 */
    public const WITH_ALL_ADDITION_VERIFICATION_PARAMETERS = 0b1111;
    // errors codes
    public const E_IS_EMPTY = 'eIsEmpty';

    private const ALLOWED_VARIABLE_TYPES = [
        VariableType::STRING,
        VariableType::INTEGER,
        VariableType::DOUBLE,
        VariableType::ARRAY,
        VariableType::OBJECT,
        VariableType::BOOLEAN,
        VariableType::NULL,
    ];

    /**
     * Boolval constructor.
     *
     * @param int                          $flags   can be a combination of the following flags:
     * @param iterable<string, mixed>|null $options Secondary validator options, such as the Translator, the length of
     *                                              the error message, hiding the value being checked, defining your
     *                                              own error messages, and so on.
     */
    public function __construct(int $flags = self::WITHOUT_ADDITIONAL_VERIFICATION_PARAMETERS, iterable $options = null)
    {
        $this->setFlags($flags);
        parent::__construct(...$this->getValidOptionsForParent($options));
        $this->initErrorMessagesTemplates([self::E_IS_EMPTY => 'The value is required and cannot be empty!']);
        $this->initErrorMessagesVars([self::MESSAGE_VAR_TYPES_EXPECTED => self::ALLOWED_VARIABLE_TYPES]);
    }


    /**
     * @param string|int|float|iterable|object|bool|null $value
     *
     * @param null                                       $context
     *
     * @return bool
     */
    public function isValid($value, $context = null): bool
    {
        if (false === in_array(gettype($value), self::ALLOWED_VARIABLE_TYPES)) {
            $this->error(self::E_INVALID_TYPE);

            return false;
        }
        if (is_object($value)) {
            if ($this->is(self::OBJECT_AS_STRING)) {
                $value = method_exists($value, '__toString') ? (string)$value : false;
            }
            if ($this->is(self::OBJECT_AS_COUNTABLE)) {
                $value = $value instanceof Countable ? $value->count() : false;
            }
        }
        if (is_numeric($value)) {
            if ($this->is(self::ALLOW_ZERO)) {
                return true;
            }
            $value = (float)$value;
        }
        if (is_iterable($value)) {
            $value = ArrayUtils::iteratorToArray($value);
        }
        if (is_string($value) && $this->is(self::DISALLOW_STR_CONTAINS_ONLY_SPACE)) {
            $value = preg_replace('`\s+`', '', $value);
        }
        if (\boolval($value)) {
            return true;
        }
        $this->error(self::E_IS_EMPTY, [self::MESSAGE_VAR_VALUE => $value]);

        return false;
    }
}
