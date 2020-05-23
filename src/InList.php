<?php

declare(strict_types=1);

namespace Estasi\Validator;

use Ds\Vector;

use function in_array;

/**
 * Class InList
 *
 * @package Estasi\Validator
 */
final class InList extends Abstracts\Validator
{
    // names of constructor parameters to create via the factory
    public const OPT_LIST   = 'list';
    public const OPT_STRICT = 'strict';
    // default values for constructor parameters
    public const STRICT_IDENTITY_VERIFICATION     = true;
    public const NON_STRICT_IDENTITY_VERIFICATION = false;
    // errors codes
    public const E_NOT_FOUND = 'eNotFound';

    private iterable $list;
    private bool     $strict;

    /**
     * InList constructor.
     *
     * @param iterable<mixed>              $list    The list in which the search for the desired value will be
     *                                              performed
     * @param bool                         $strict  If true (recommended), the comparison is performed with type
     *                                              conversion (===); If false, it is performed without type conversion
     *                                              (==)
     * @param iterable<string, mixed>|null $options Secondary validator options, such as the Translator, the length of
     *                                              the error message, hiding the value being checked, defining your
     *                                              own error messages, and so on.
     */
    public function __construct(
        iterable $list,
        bool $strict = self::STRICT_IDENTITY_VERIFICATION,
        iterable $options = null
    ) {
        $this->list   = new Vector($list);
        $this->strict = $strict;
        parent::__construct(...$this->createProperties($options));
        $this->initErrorMessagesTemplates(
            [self::E_NOT_FOUND => 'The desired value "%value%" was not found in the list!']
        );
    }

    /**
     * @inheritDoc
     */
    public function isValid($value, $context = null): bool
    {
        $isValid = $this->strict
            ? $this->list->contains($value)
            : in_array($value, $this->list->toArray(), $this->strict);

        if ($isValid) {
            return true;
        }
        $this->error(self::E_NOT_FOUND, [self::MESSAGE_VAR_VALUE => $value]);

        return false;
    }
}
