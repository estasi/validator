<?php

declare(strict_types=1);

namespace Estasi\Validator;

use Ds\PriorityQueue;
use Estasi\Utility\{
    ArrayUtils,
    Traits\Errors,
    Traits\TopPriority
};
use Estasi\Validator\Interfaces\PluginManager;
use Estasi\Validator\Interfaces\Validator;
use InvalidArgumentException;

use function is_iterable;
use function is_string;
use function sprintf;

/**
 * Class Chain
 *
 * @package Estasi\Validator
 */
final class Chain implements Interfaces\Chain
{
    use Traits\Validation;
    use Errors;
    use TopPriority;

    private ?PluginManager $pluginManager;
    private PriorityQueue  $validators;

    /**
     * @inheritDoc
     */
    public function __construct(
        ?PluginManager $pluginManager = self::DEFAULT_PLUGIN_MANAGER,
        ...$validators
    ) {
        $this->pluginManager = $pluginManager ?? new \Estasi\Validator\PluginManager();
        $this->putAll($validators);
    }

    /**
     * @inheritDoc
     */
    public function attach(
        $validator,
        bool $breakOnFailure = self::WITHOUT_BREAK_ON_FAILURE,
        int $priority = 1
    ): Interfaces\Chain {
        $new = clone $this;
        $new->processing($validator, $breakOnFailure, $priority);

        return $new;
    }

    /**
     * @inheritDoc
     */
    public function prepend(
        $validator,
        bool $breakOnFailure = self::WITHOUT_BREAK_ON_FAILURE
    ): Interfaces\Chain {
        return $this->attach($validator, $breakOnFailure, $this->getTopPriority());
    }

    /**
     * @inheritDoc
     */
    public function putAll(iterable $validators): void
    {
        $this->validators = new PriorityQueue();
        foreach ($validators as $validator) {
            $this->processing($validator);
        }
    }

    /**
     * @inheritDoc
     */
    public function count()
    {
        return $this->validators->count();
    }

    /**
     * @inheritDoc
     */
    public function isValid($value, $context = null): bool
    {
        $isValid = true;
        /**
         * @var \Estasi\Validator\Interfaces\Validator $validator
         * @var bool                                   $breakOnFailure
         */
        foreach ($this->validators->copy() as [$validator, $breakOnFailure]) {
            if (false === $validator->isValid($value, $context)) {
                $isValid = false;
                $this->mergeErrors($validator->getLastErrors());
                if ($breakOnFailure) {
                    break;
                }
            }
        }

        return $isValid;
    }

    /**
     * @inheritDoc
     */
    public function getValidators(): iterable
    {
        return $this->validators->copy();
    }

    public function __clone()
    {
        $validators       = $this->validators->toArray();
        $this->validators = new PriorityQueue();
        foreach ($validators as $validator) {
            $this->validators->push($validator, 1);
        }
        $this->pluginManager = clone $this->pluginManager;
    }

    /**
     * @param string|array|\Estasi\Validator\Interfaces\Validator $validator
     * @param bool                                                $breakOnFailure
     * @param int                                                 $priority
     *
     * @throws \InvalidArgumentException
     * @throws \Estasi\PluginManager\Exception\NotFoundException
     * @throws \Estasi\PluginManager\Exception\ContainerException
     */
    private function processing($validator, bool $breakOnFailure = self::WITH_BREAK_ON_FAILURE, int $priority = 1)
    {
        if (is_iterable($validator)) {
            [
                self::VALIDATOR_NAME             => $validator,
                self::VALIDATOR_OPTIONS          => $options,
                self::VALIDATOR_BREAK_ON_FAILURE => $breakOnFailureTmp,
                self::VALIDATOR_PRIORITY         => $priorityTmp,
            ] = ArrayUtils::iteratorToArray($validator);

            $breakOnFailure = $breakOnFailureTmp ?? $breakOnFailure;
            $priority       = $priorityTmp ?? $priority;
        }

        if (is_string($validator)) {
            $validator = $this->pluginManager->getValidator($validator, $options ?? []);
        }

        if (false === $validator instanceof Validator) {
            throw new InvalidArgumentException(
                sprintf(
                    'The validator is not valid! Expected: "string" - the name of the filter, "array" - containing the filter name or "object" implementing "%s"!',
                    Interfaces\Validator::class
                )
            );
        }

        $this->validators->push([$validator, $breakOnFailure], $priority);
        $this->updateTopPriority($priority);
    }
}
