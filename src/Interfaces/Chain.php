<?php

declare(strict_types=1);

namespace Estasi\Validator\Interfaces;

use Countable;

/**
 * Interface Chain
 *
 * @package Estasi\Validator\Interfaces
 */
interface Chain extends Validator, Countable
{
    public const DEFAULT_PLUGIN_MANAGER = null;

    // Names of attach and prepend parameters for creating an object through the factory
    public const VALIDATOR_NAME             = 'validator';
    public const VALIDATOR_OPTIONS          = 'options';
    public const VALIDATOR_BREAK_ON_FAILURE = 'breakOnFailure';
    public const VALIDATOR_PRIORITY         = 'priority';

    public const WITH_BREAK_ON_FAILURE    = true;
    public const WITHOUT_BREAK_ON_FAILURE = false;

    /**
     * Returns the validator queue and the breakOnFailure value of this validator
     *
     * @return iterable<array> [\Estasi\Validator\Interfaces\Validator, bool]
     */
    public function getValidators(): iterable;

    /**
     * Attaches the validator to the queue
     *
     * If breakChainOnFailure is true, then if the validator fails, the next validator in the chain, if one exists,
     * will not be executed.
     *
     * This method MUST be implemented in a way that preserves the chain immutability, and must return an instance that
     * has a modified call chain
     *
     * @param string|iterable<string, mixed>|\Estasi\Validator\Interfaces\Validator $validator      takes as a parameter
     *                                                                                              a string (the name
     *                                                                                              of the Validator
     *                                                                                              class), a Validator
     *                                                                                              object, or an array
     *                                                                                              of keys (validator,
     *                                                                                              options,
     *                                                                                              message_template)
     * @param bool                                                                  $breakOnFailure interrupting chain
     *                                                                                              execution when
     *                                                                                              receiving FALSE from
     *                                                                                              validator
     * @param int                                                                   $priority       Priority at which to
     *                                                                                              enqueue validator;
     *                                                                                              defaults to 1
     *                                                                                              (higher executes
     *                                                                                              earlier)
     *
     * @return $this
     * @api
     */
    public function attach($validator, bool $breakOnFailure = self::WITHOUT_BREAK_ON_FAILURE, int $priority = 1): self;

    /**
     * Attaches the validator to the top of the queue
     *
     * If breakChainOnFailure is true, then if the validator fails, the next validator in the chain, if one exists,
     * will not be executed.
     *
     * This method MUST be implemented in a way that preserves the chain immutability, and must return an instance that
     * has a modified call chain
     *
     * @param string|iterable<string, mixed>|\Estasi\Validator\Interfaces\Validator $validator      takes as a parameter
     *                                                                                              a string (the name
     *                                                                                              of the Validator
     *                                                                                              class), a Validator
     *                                                                                              object, or an array
     *                                                                                              of keys (validator,
     *                                                                                              options,
     *                                                                                              message_template)
     * @param bool                                                                  $breakOnFailure interrupting chain
     *                                                                                              execution when
     *                                                                                              receiving FALSE from
     *                                                                                              validator
     *
     * @return $this
     * @api
     */
    public function prepend($validator, bool $breakOnFailure = self::WITHOUT_BREAK_ON_FAILURE): self;

    /**
     * Attaches the entire chain at once. Analogous to the entry in the class constructor.
     *
     * ATTENTION!!! When writing, the chain created in the constructor MUST be cleared.
     *
     * @param iterable<string, mixed>|\Estasi\Validator\Interfaces\Validator[] $validators
     *
     * @internal
     */
    public function putAll(iterable $validators): void;

    /**
     * Chain constructor.
     *
     * @param \Estasi\Validator\Interfaces\PluginManager|null                       $pluginManager
     * @param string|iterable<string, mixed>|\Estasi\Validator\Interfaces\Validator ...$validators
     */
    public function __construct(?PluginManager $pluginManager = self::DEFAULT_PLUGIN_MANAGER, ...$validators);
}
