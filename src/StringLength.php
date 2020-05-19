<?php

declare(strict_types=1);

namespace Estasi\Validator;

use Estasi\Utility\Interfaces\Charset;
use Estasi\Utility\Interfaces\VariableType;

use function is_string;
use function mb_strlen;

/**
 * Class StringLength
 *
 * @package Estasi\Validator
 */
final class StringLength extends Abstracts\Validator implements Interfaces\Min, Interfaces\Max
{
    // default values for constructor parameters
    public const NO_LENGTH_LIMITATION = -1;
    public const DEFAULT_ENCODING     = Charset::UTF_8;

    private GreaterThan $greaterThan;
    private ?LessThan   $lessThan;
    private string      $encoding;

    /**
     * StringLength constructor.
     *
     * @param int                          $min      The minimum length of the string. values less than zero will be
     *                                               equal to zero
     * @param int                          $max      The maximum length of the string. values less than zero will be
     *                                               equal to -1 (no limit)
     * @param string                       $encoding The character encoding
     * @param iterable<string, mixed>|null $options  Secondary validator options, such as the Translator, the length of
     *                                               the error message, hiding the value being checked, defining your
     *                                               own error messages, and so on.
     */
    public function __construct(
        int $min = 0,
        int $max = self::NO_LENGTH_LIMITATION,
        string $encoding = self::DEFAULT_ENCODING,
        iterable $options = null
    ) {
        $this->greaterThan = new GreaterThan($min < 0 ? 0 : $min, GreaterThan::INCLUSIVELY, $options);
        $this->lessThan    = $max > self::NO_LENGTH_LIMITATION
            ? new LessThan($max, LessThan::INCLUSIVELY, $options)
            : null;
        $this->encoding    = $encoding;
        parent::__construct(...$this->getValidOptionsForParent($options));
        $this->initErrorMessagesVars([self::MESSAGE_VAR_TYPES_EXPECTED => VariableType::STRING]);
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

        $lengthString = mb_strlen($value, $this->encoding);
        if (false === $this->greaterThan->isValid($lengthString, $context)) {
            $this->setErrors($this->greaterThan->getLastErrors());

            return false;
        }
        if (isset($this->lessThan) && false === $this->lessThan->isValid($lengthString, $context)) {
            $this->setErrors($this->greaterThan->getLastErrors());

            return false;
        }

        return true;
    }
}
