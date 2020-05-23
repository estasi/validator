<?php

declare(strict_types=1);

namespace Estasi\Validator;

use Closure;
use Ds\Vector;
use Exception;

use function call_user_func_array;

/**
 * Class Callback
 *
 * Call a callback with an array of parameters
 *
 * @package Estasi\Validator
 */
final class Callback extends Abstracts\Validator
{
    // names of constructor parameters to create via the factory
    public const OPT_CALLBACK = 'callback';
    public const OPT_PARAMS   = 'params';
    // default values for constructor parameters
    public const WITHOUT_CALLBACK_PARAMS = null;
    // errors codes
    public const E_INVALID_CALLBACK = 'eInvalidCallback';
    public const E_INVALID_VALUE    = 'eInvalidValue';

    private Closure   $callback;
    private ?iterable $params;

    /**
     * Callback constructor.
     *
     * @param callable                     $callback The callable to be called.
     * @param iterable<int, mixed>|null    $params   The parameters to be passed to the callback, as an indexed array.
     * @param iterable<string, mixed>|null $options  Secondary validator options, such as the Translator, the length of
     *                                               the error message, hiding the value being checked, defining your
     *                                               own error messages, and so on.
     */
    public function __construct(
        callable $callback,
        iterable $params = self::WITHOUT_CALLBACK_PARAMS,
        iterable $options = null
    ) {
        $this->callback = $callback;
        $this->params   = new Vector($params ?? []);
        parent::__construct(...$this->createProperties($options));
        $this->initErrorMessagesTemplates(
            [
                self::E_INVALID_CALLBACK => 'An exception has been raised within the callback!',
                self::E_INVALID_VALUE    => 'The input value "%value%" was not checked!',
            ]
        );
    }

    /**
     * @inheritDoc
     */
    public function isValid($value, $context = null): bool
    {
        try {
            $this->params->unshift($value);
            if (false === (bool)call_user_func_array($this->callback, $this->params->toArray())) {
                $this->error(self::E_INVALID_VALUE, [self::MESSAGE_VAR_VALUE => $value]);

                return false;
            }
        } catch (Exception $exception) {
            $this->error(self::E_INVALID_CALLBACK);

            return false;
        }

        return true;
    }
}
