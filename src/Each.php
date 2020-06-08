<?php

declare(strict_types=1);

namespace Estasi\Validator;

use Ds\Vector;
use Estasi\Utility\Interfaces\VariableType;
use Estasi\Validator\Interfaces\Validator;
use Traversable;

use function explode;
use function is_iterable;
use function is_numeric;
use function is_string;

/**
 * Class Each
 *
 * @package Estasi\Validator
 */
final class Each extends Abstracts\Validator
{
    // names of constructor parameters to create via the factory
    public const OPT_VALIDATOR = 'validator';
    public const OPT_DELIMITER = 'delimiter';
    // default values for constructor parameters
    public const WITHOUT_DELIMITER = null;

    private Validator $validator;
    private ?string   $delimiter;

    /**
     * Each constructor.
     *
     * @param \Estasi\Validator\Interfaces\Validator $validator Validator that will check all elements of the array
     * @param string|null                            $delimiter The separator string for elements in the array
     * @param iterable<string, mixed>|null           $options   Secondary validator options, such as the Translator, the
     *                                                          length of the error message, hiding the value being
     *                                                          checked, defining your own error messages, and so on.
     */
    public function __construct(
        Validator $validator,
        ?string $delimiter = self::WITHOUT_DELIMITER,
        iterable $options = null
    ) {
        $this->validator = $validator;
        $this->delimiter = $delimiter;
        parent::__construct(...$this->createProperties($options));
        $this->initErrorMessagesVars(
            [
                self::MESSAGE_VAR_TYPES_EXPECTED => [
                    VariableType::STRING,
                    VariableType::INTEGER,
                    VariableType::DOUBLE,
                    VariableType::ARRAY,
                    Traversable::class,
                ],
            ]
        );
    }

    /**
     * @param string|int|float|iterable $value
     * @param null                      $context
     *
     * @return bool
     */
    public function isValid($value, $context = null): bool
    {
        if (is_numeric($value)) {
            $value = [$value];
        }
        if (is_string($value)) {
            $value = $this->delimiter ? explode($this->delimiter, $value) : [$value];
        }
        if (is_iterable($value)) {
            $value = new Vector($value);
        } else {
            $this->error(self::E_INVALID_TYPE);

            return false;
        }

        foreach ($value as $item) {
            if (false === $this->validator->isValid($item, $context)) {
                $this->setErrors($this->validator->getLastError());

                return false;
            }
        }

        return true;
    }

    /**
     * Returns the validator
     *
     * @return \Estasi\Validator\Interfaces\Validator
     */
    public function getValidator(): Validator
    {
        return $this->validator;
    }
}
