<?php

declare(strict_types=1);

namespace Estasi\Validator;

use Ds\Map;

/**
 * Class UUID
 *
 * @package Estasi\Validator
 */
final class UUID extends Abstracts\Validator
{
    // errors codes
    public const E_NOT_UUID = 'eNotUUID';

    private Regex $validator;

    /**
     * UUID constructor.
     *
     * @param iterable<string, mixed>|null $options Secondary validator options, such as the Translator, the length of
     *                                              the error message, hiding the value being checked, defining your
     *                                              own error messages, and so on.
     *
     * @noinspection PhpMissingParentConstructorInspection
     */
    public function __construct(iterable $options = null)
    {
        $this->validator = new Regex(
            '`^[0-9a-f]{8}\x2D[0-9a-f]{4}\x2D[0-9a-f]{4}\x2D[0-9a-f]{4}\x2D[0-9a-f]{12}$`i',
            Regex::OFFSET_ZERO,
            (new Map($options ?? []))->merge(
                [Regex::E_NOT_MATCH => 'The checked value "%value%" does not match the UUID format!']
            )
        );
    }

    /**
     * @inheritDoc
     */
    public function isValid($value, $context = null): bool
    {
        if ($this->validator->isValid($value, $context)) {
            return true;
        }
        if ($this->validator->isLastError(Regex::E_NOT_MATCH)) {
            $this->setError(self::E_NOT_UUID, $this->validator->getLastErrorMessage());
        } else {
            $this->setErrors($this->validator->getLastErrors());
        }

        return false;
    }
}
