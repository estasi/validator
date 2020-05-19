<?php

declare(strict_types=1);

namespace Estasi\Validator\Interfaces;

/**
 * Interface Inclusive
 *
 * @package Estasi\Validator\Interfaces
 */
interface Inclusive
{
    // names of constructor parameters to create via the factory
    public const OPT_INCLUSIVE = 'inclusive';
    // default values for constructor parameters
    public const INCLUSIVELY   = true;
    public const NOT_INCLUSIVE = false;
}
