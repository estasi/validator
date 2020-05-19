<?php

declare(strict_types=1);

namespace Estasi\Validator;

use Estasi\Utility\{
    Interfaces\Uri,
    Interfaces\VariableType
};

use function filter_var;
use function idn_to_ascii;
use function is_string;
use function sprintf;
use function strstr;
use function substr;

use const FILTER_FLAG_EMAIL_UNICODE;

/**
 * Class Email
 *
 * Validates whether the value is a valid e-mail address.
 * In general, this validates e-mail addresses against the syntax in RFC 822, with the exceptions that comments and
 * whitespace folding and dotless domain names are not supported.
 *
 * @link    https://www.php.net/manual/en/filter.filters.validate.php
 *
 * @package Estasi\Validator
 */
final class Email extends Abstracts\Validator
{
    // names of constructor parameters to create via the factory
    public const OPT_ALLOW_UNICODE = 'allowUnicode';
    // default values for constructor parameters
    public const ALLOW_UNICODE   = true;
    public const DISABLE_UNICODE = false;
    // error code
    public const E_INVALID_EMAIL = 'eInvalidEmail';

    private bool $allowUnicode;

    /**
     * Email constructor.
     *
     * @param bool                         $allowUnicode Allows the email address to contain Unicode characters.
     * @param iterable<string, mixed>|null $options      Secondary validator options, such as the Translator, the
     *                                                   length of the error message, hiding the value being checked,
     *                                                   defining your own error messages, and so on.
     */
    public function __construct(bool $allowUnicode = self::DISABLE_UNICODE, iterable $options = null)
    {
        $this->allowUnicode = $allowUnicode;
        parent::__construct(...$this->getValidOptionsForParent($options));
        $this->initErrorMessagesTemplates([self::E_INVALID_EMAIL => 'Email "%value%" is not correct!']);
        $this->initErrorMessagesVars([self::MESSAGE_VAR_TYPES_EXPECTED => [VariableType::STRING, Uri::class]]);
    }

    /**
     * @param string|\Estasi\Utility\Interfaces\Uri $value
     *
     * @param null                                  $context
     *
     * @return bool
     */
    public function isValid($value, $context = null): bool
    {
        if ($value instanceof Uri) {
            $value = $value->path;
        }
        if (false === is_string($value)) {
            $this->error(self::E_INVALID_TYPE);

            return false;
        }

        $options = null;
        if ($this->allowUnicode) {
            $options = FILTER_FLAG_EMAIL_UNICODE;
            $value   = sprintf(
                "%s@%s",
                idn_to_ascii(strstr($value, '@', true)),
                idn_to_ascii(substr(strstr($value, '@'), 1))
            );
        }

        $filtered = filter_var($value, FILTER_VALIDATE_EMAIL, $options);
        if (false === $filtered) {
            $this->error(self::E_INVALID_EMAIL, [self::MESSAGE_VAR_VALUE => $value]);

            return false;
        }

        return true;
    }
}
