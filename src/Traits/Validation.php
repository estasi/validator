<?php

declare(strict_types=1);

namespace Estasi\Validator\Traits;

/**
 * Trait Validation
 *
 * @package Estasi\Validator\Traits
 */
trait Validation
{
    /**
     * @inheritDoc
     */
    final public function __invoke($value, $context = null): bool
    {
        return $this->isValid($value, $context);
    }

    /**
     * @inheritDoc
     */
    final public function notValid($value, $context = null): bool
    {
        return false === $this->isValid($value, $context);
    }
}
