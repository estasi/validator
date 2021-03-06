<?php

declare(strict_types=1);

namespace Estasi\Validator\Abstracts;

use Ds\{
    Map,
    Vector
};
use Estasi\Translator\Interfaces\Translator;
use Estasi\Utility\{
    Traits\Disable__call,
    Traits\Disable__callStatic,
    Traits\Disable__set,
    Traits\Errors,
    Traits\Properties__get,
    Traits\ReceivedTypeForException
};
use Estasi\Validator\{
    Interfaces\Validator as ValidatorInterface,
    Traits\Validation
};

use function compact;
use function get_class;
use function is_iterable;
use function is_null;
use function is_object;
use function mb_strlen;
use function method_exists;
use function preg_replace_callback;
use function sprintf;
use function str_repeat;
use function substr;

/**
 * Class Validator
 *
 * @property-read Translator|null $translator
 * @property-read Map             $errorMessagesAliases
 * @property-read string|null     $errorValueAlias
 * @property-read int             $errorMessageLength
 * @property-read bool            $errorValueObscured
 * @package Estasi\Validator\Abstracts
 */
abstract class Validator implements ValidatorInterface
{
    use Validation;
    use Errors;
    use Disable__set;
    use Disable__call;
    use Disable__callStatic;
    use Properties__get;
    use ReceivedTypeForException;

    // names of constructor parameters to create via the factory
    public const OPT_TRANSLATOR             = 'translator';
    public const OPT_ERROR_MESSAGES_ALIASES = 'errorMessagesAliases';
    public const OPT_ERROR_VALUE_ALIAS      = 'errorValueAlias';
    public const OPT_ERROR_MESSAGE_LENGTH   = 'errorMessageLength';
    public const OPT_ERROR_VALUE_OBSCURED   = 'errorValueObscured';
    public const OPT_OPTIONS                = 'options';
    // default values for constructor parameters
    public const WITHOUT_TRANSLATOR                 = null;
    public const NO_ALIASES_FOR_ERROR_MESSAGES      = null;
    public const NO_ALIAS_VALUE_IN_ERROR_MESSAGE    = null;
    public const ERROR_MESSAGE_WITHOUT_LENGTH_LIMIT = -1;
    public const DO_NOT_OBSCURE_VALUE               = false;
    public const OBSCURE_VALUE                      = true;
    // errors codes
    public const E_INVALID_TYPE = 'eInvalidType';
    // main variables of error message templates
    protected const MESSAGE_VAR_VALUE          = 'value';
    protected const MESSAGE_VAR_TYPES_EXPECTED = 'types';

    private Map $properties;
    private Map $errorMessages;
    private Map $errorMessagesVars;

    /**
     * Validator constructor.
     *
     * @param \Estasi\Translator\Interfaces\Translator|null $translator
     * @param iterable<string, string>|null                 $errorMessagesAliases
     * @param string|null                                   $errorValueAlias
     * @param int                                           $errorMessageLength
     * @param bool                                          $errorValueObscured
     * @param array                                         $options
     */
    public function __construct(
        ?Translator $translator,
        ?iterable $errorMessagesAliases,
        ?string $errorValueAlias,
        int $errorMessageLength,
        bool $errorValueObscured,
        array $options = []
    ) {
        $errorMessagesAliases = new Map($errorMessagesAliases ?? []);
        $errorMessageLength   = $errorMessageLength < self::ERROR_MESSAGE_WITHOUT_LENGTH_LIMIT
            ? self::ERROR_MESSAGE_WITHOUT_LENGTH_LIMIT
            : $errorMessageLength;
        // setting basic properties
        $this->properties = new Map(
            compact('translator', 'errorMessagesAliases', 'errorValueAlias', 'errorMessageLength', 'errorValueObscured')
        );
        // setting properties of a child class (a specific validator)
        $this->properties->putAll($options);

        $this->initErrorMessagesTemplates([self::E_INVALID_TYPE => 'The data type is not valid. Expected: "%types%"!']);
        $this->initErrorMessagesVars([]);
    }

    /**
     * Returns a list with the values of parameters for the constructor of the Estasi\Validator\Abstracts\Validator
     * class
     *
     * @param iterable|null $options    Basic validator options, such as the Translator, the length of the error
     *                                  message, hiding the value being checked, defining your own error messages, and
     *                                  so on.
     * @param array         $properties properties of a child class (a specific validator)
     *
     * @return array<int, mixed>
     */
    protected function createProperties(?iterable $options, array $properties = []): array
    {
        $default = new Map(
            [
                self::OPT_TRANSLATOR             => self::WITHOUT_TRANSLATOR,
                self::OPT_ERROR_MESSAGES_ALIASES => self::NO_ALIASES_FOR_ERROR_MESSAGES,
                self::OPT_ERROR_VALUE_ALIAS      => self::NO_ALIAS_VALUE_IN_ERROR_MESSAGE,
                self::OPT_ERROR_MESSAGE_LENGTH   => self::ERROR_MESSAGE_WITHOUT_LENGTH_LIMIT,
                self::OPT_ERROR_VALUE_OBSCURED   => self::DO_NOT_OBSCURE_VALUE,
                self::OPT_OPTIONS                => $properties,
            ]
        );

        return $default->merge(new Map($options ?? []))
                       ->intersect($default)
                       ->values()
                       ->toArray();
    }

    /**
     * @param iterable<string, mixed>|null $options
     *
     * @return array<int, mixed>
     * @deprecated
     */
    final protected function getValidOptionsForParent(iterable $options = null): array
    {
        if (is_null($options)) {
            return [];
        }
        $default = new Map(
            [
                self::OPT_TRANSLATOR             => self::WITHOUT_TRANSLATOR,
                self::OPT_ERROR_MESSAGES_ALIASES => self::NO_ALIASES_FOR_ERROR_MESSAGES,
                self::OPT_ERROR_VALUE_ALIAS      => self::NO_ALIAS_VALUE_IN_ERROR_MESSAGE,
                self::OPT_ERROR_MESSAGE_LENGTH   => self::ERROR_MESSAGE_WITHOUT_LENGTH_LIMIT,
                self::OPT_ERROR_VALUE_OBSCURED   => self::DO_NOT_OBSCURE_VALUE,
            ]
        );

        return $default->merge(new Map($options))
                       ->intersect($default)
                       ->values()
                       ->toArray();
    }

    /**
     * @param iterable<string, string> $templates
     */
    final protected function initErrorMessagesTemplates(iterable $templates): void
    {
        if (isset($this->errorMessages)) {
            /** @noinspection PhpParamsInspection */
            $this->errorMessages->putAll($templates);
        } else {
            $this->errorMessages = new Map($templates);
        }
    }

    /**
     * @param iterable<string, string> $variables
     */
    final protected function initErrorMessagesVars(iterable $variables): void
    {
        if (isset($this->errorMessagesVars)) {
            /** @noinspection PhpParamsInspection */
            $this->errorMessagesVars->putAll($variables);
        } else {
            $this->errorMessagesVars = new Map($variables);
        }
    }

    /**
     * @param mixed $value
     */
    final protected function setValue($value): void
    {
        $this->errorMessagesVars->put(self::MESSAGE_VAR_VALUE, $value);
    }

    /**
     * Creates an error message from a template and writes it to the stack
     *
     * @param string                  $code    code of the error message
     * @param iterable<string, mixed> $context variables in error message templates that cannot be determined when
     *                                         initializing an object
     */
    final protected function error(string $code, iterable $context = []): void
    {
        $message = $this->errorMessagesAliases->hasKey($code)
            ? $this->errorMessagesAliases->get($code)
            : $this->errorMessages->get($code, null);
        if (is_null($message)) {
            return;
        }

        if (isset($this->translator)) {
            $message = $this->translator->gettext($message);
        }

        $this->errorMessagesVars->putAll($context);
        $message = preg_replace_callback('`%(?P<var>\w+)%`', [$this, 'replaceVarToValue'], $message);

        // trim the message to the specified length
        if (self::ERROR_MESSAGE_WITHOUT_LENGTH_LIMIT !== $this->errorMessageLength
            && mb_strlen($message) > $this->errorMessageLength) {
            $message = sprintf('%s...', substr($message, 0, $this->errorMessageLength - 3));
        }

        $this->setError($code, $message);
    }

    private function replaceVarToValue(array $matches): string
    {
        $variable = $this->errorValueAlias === $matches['var'] ? self::MESSAGE_VAR_VALUE : $matches['var'];

        if ($this->errorMessagesVars->hasKey($variable)) {
            $value = $this->errorMessagesVars->get($variable);
            if (is_iterable($value)) {
                $value = sprintf('[%s]', (new Vector($value))->join(', '));
            } elseif (is_object($value) && false === method_exists($value, '__toString')) {
                $value = sprintf('%s object', get_class($value));
            } else {
                $value = (string)$value;
            }
            if (self::MESSAGE_VAR_VALUE === $variable && $this->errorValueObscured) {
                $value = str_repeat('*', mb_strlen($value));
            }

            return $value;
        }

        return $matches[0];
    }
}
