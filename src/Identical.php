<?php

declare(strict_types=1);

namespace Estasi\Validator;

use Estasi\Utility\{
    ArrayUtils,
    Interfaces\VariableType
};

use function boolval;
use function gettype;
use function is_iterable;
use function is_null;

/**
 * Class Identical
 *
 * @package Estasi\Validator
 */
final class Identical extends Abstracts\Validator
{
    // names of constructor parameters to create via the factory
    public const OPT_TOKEN  = 'token';
    public const OPT_STRICT = 'strict';
    // default values for constructor parameters
    public const STRICT_IDENTITY_VERIFICATION     = true;
    public const NON_STRICT_IDENTITY_VERIFICATION = false;
    // errors codes
    public const E_MISSING_TOKEN = 'eMissingToken';
    public const E_NOT_SAME      = 'eNotSame';

    /** @var mixed */
    private      $token;
    private bool $strict;

    /**
     * Identical constructor.
     *
     * @param mixed                        $token   a token for comparison with the value being checked or a key for
     *                                              searching for a value in the data array obtained in the context
     *                                              parameter of the isValid() method
     * @param bool                         $strict  if true, the comparison is performed with type conversion (===);
     *                                              if false, it is performed without type conversion (==)
     * @param iterable<string, mixed>|null $options Secondary validator options, such as the Translator, the length of
     *                                              the error message, hiding the value being checked, defining your
     *                                              own error messages, and so on.
     */
    public function __construct($token, bool $strict = self::NON_STRICT_IDENTITY_VERIFICATION, iterable $options = null)
    {
        $this->token  = $token;
        $this->strict = $strict;
        parent::__construct(...$this->getValidOptionsForParent($options));
        $this->initErrorMessagesTemplates(
            [
                self::E_MISSING_TOKEN => 'A token was not found to compare the value!',
                self::E_NOT_SAME      => 'The compared values "%value%" and "%token%" do not match!',
            ]
        );
    }

    /**
     * @inheritDoc
     */
    public function isValid($value, $context = null): bool
    {
        $token = $this->getToken($context);
        if (is_null($token)) {
            $this->error(self::E_MISSING_TOKEN);

            return false;
        }

        if ($this->strict) {
            if ($value === $token) {
                return true;
            }
        } elseif ($value == $token) {
            return true;
        }
        $this->error(self::E_NOT_SAME, [self::MESSAGE_VAR_VALUE => $value, self::OPT_TOKEN => $token]);

        return false;
    }

    private function getToken($context)
    {
        if (boolval($context)) {
            if (is_iterable($context)) {
                switch (gettype($this->token)) {
                    case VariableType::STRING:
                        $token = ArrayUtils::get($this->token, $context);
                        break;
                    case VariableType::INTEGER:
                    case VariableType::DOUBLE:
                        $token = ArrayUtils::iteratorToArray($context)[$this->token];
                        break;
                    default:
                        $token = $context;
                        break;
                }
            } else {
                $token = $context;
            }
        } else {
            $token = $this->token;
        }

        return $token;
    }
}
