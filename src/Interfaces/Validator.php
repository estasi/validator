<?php

declare(strict_types=1);

namespace Estasi\Validator\Interfaces;

use Estasi\Utility\Interfaces\Errors;

/**
 * Interface Validator
 *
 * @package Estasi\Validator\Interfaces
 */
interface Validator extends Errors
{

    /**
     * Returns TRUE if value meets the validation requirements,
     * if value does not pass validation, this method returns FALSE.
     *
     * @param mixed                               $value   value to check
     * @param string|int|float|iterable|bool|null $context additional data for checking the value
     *
     * @return bool
     * @api
     */
    public function isValid($value, $context = null): bool;

    /**
     * Returns TRUE if value does not meet validation requirements,
     * if value passes validation, this method returns FALSE.
     *
     * @param mixed                               $value   value to check
     * @param string|int|float|iterable|bool|null $context additional data for checking the value
     *
     * @return bool
     * @api
     */
    public function notValid($value, $context = null): bool;

    /**
     * Returns TRUE if value meets the validation requirements,
     * if value does not pass validation, this method returns FALSE.
     *
     * @param mixed                               $value   value to check
     * @param string|int|float|iterable|bool|null $context additional data for checking the value
     *
     * @return bool
     */
    public function __invoke($value, $context = null): bool;
}
