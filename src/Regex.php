<?php

declare(strict_types=1);

namespace Estasi\Validator;

use Estasi\Utility\{
    Interfaces\VariableType,
    Traits\GetPatternsHtml5AndPcre
};

use function compact;
use function is_string;
use function preg_match;

/**
 * Class Regex
 *
 * @property-read array<string, string> $pattern ['pcre' => 'string', 'html' => 'string']
 * @property-read int                   $offset
 * @package Estasi\Validator
 */
final class Regex extends Abstracts\Validator
{
    use GetPatternsHtml5AndPcre;

    // names of constructor parameters to create via the factory
    public const OPT_PATTERN = 'pattern';
    public const OPT_OFFSET  = 'offset';
    // default values for constructor parameters
    public const OFFSET_ZERO = 0;
    // error code
    public const E_EMPTY_PATTERN = 'eEmptyPattern';
    public const E_ERROROUS      = 'eRegexErrorous';
    public const E_NOT_MATCH     = 'eRegexNotMatch';

    /**
     * Regex constructor.
     *
     * @param string                       $pattern The pattern for searching in PCRE or html 5 format
     * @param int                          $offset  Normally, the search starts from the beginning of the subject
     *                                              string. The optional parameter offset can be used to specify the
     *                                              alternate place from which to start the search (in bytes).
     * @param iterable<string, mixed>|null $options Secondary validator options, such as the Translator, the length of
     *                                              the error message, hiding the value being checked, defining your
     *                                              own error messages, and so on.
     */
    public function __construct(string $pattern, int $offset = self::OFFSET_ZERO, iterable $options = null)
    {
        $pattern = $this->getPatternsHtml5AndPCRE($pattern);
        parent::__construct(...$this->createProperties($options, compact('pattern', 'offset')));
        $this->initErrorMessagesTemplates(
            [
                self::E_EMPTY_PATTERN => 'The pattern must not be an empty string!',
                self::E_ERROROUS      => 'An internal error occurred while using template "%pattern%"!',
                self::E_NOT_MATCH     => 'The entered value "%value%" does not match the pattern "%pattern%"!',
            ]
        );
        $this->initErrorMessagesVars(
            [self::MESSAGE_VAR_TYPES_EXPECTED => VariableType::STRING, self::OPT_PATTERN => $this->pattern]
        );
    }

    /**
     * @param string $value
     *
     * @param null   $context
     *
     * @return bool
     */
    public function isValid($value, $context = null): bool
    {
        if (false === is_string($value)) {
            $this->error(self::E_INVALID_TYPE);

            return false;
        }

        if (empty($this->pattern['pcre'])) {
            $this->error(self::E_EMPTY_PATTERN);

            return false;
        }

        switch (preg_match($this->pattern['pcre'], $value, $matches, 0, $this->offset)) {
            case 0:
                $this->error(self::E_NOT_MATCH, [self::MESSAGE_VAR_VALUE => $value]);

                return false;
            case false:
                $this->error(self::E_ERROROUS);

                return false;
            default:
                return true;
        }
    }
}
