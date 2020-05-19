<?php

declare(strict_types=1);

namespace Estasi\Validator;

use DateTime;
use DateTimeInterface;
use Estasi\Utility\Interfaces\VariableType;
use IntlDateFormatter;

use function date_create;
use function gettype;
use function method_exists;
use function sprintf;
use function ucfirst;

/**
 * Class Date
 *
 * @package Estasi\Validator
 */
final class Date extends Abstracts\Validator
{
    use Traits\ConvertArrayToDateString;
    use Traits\ISODate;

    // names of constructor parameters to create via the factory
    public const OPT_FORMAT = 'format';
    // default values for constructor parameters
    public const WITHOUT_FORMAT = null;
    // errors codes
    public const E_INVALID_DATE = 'eInvalidDate';
    public const E_FALSE_FORMAT = 'eFalseFormat';

    private ?string $format;

    /**
     * Date constructor.
     *
     * @param string|null                  $format  Format of the date to be checked for validity
     * @param iterable<string, mixed>|null $options Secondary validator options, such as the Translator, the length of
     *                                              the error message, hiding the value being checked, defining your
     *                                              own error messages, and so on.
     */
    public function __construct(?string $format = self::WITHOUT_FORMAT, iterable $options = null)
    {
        $this->format = $format;
        parent::__construct(...$this->getValidOptionsForParent($options));
        $this->initErrorMessagesTemplates(
            [
                self::E_INVALID_DATE => 'The checked value "%value%" is not a valid date!',
                self::E_FALSE_FORMAT => 'The checked value "%value%" does not match the date format "%format%"!',
            ]
        );
        $this->initErrorMessagesVars(
            [
                self::OPT_FORMAT                 => $this->format,
                self::MESSAGE_VAR_TYPES_EXPECTED => [
                    VariableType::STRING,
                    VariableType::INTEGER,
                    VariableType::DOUBLE,
                    VariableType::ARRAY,
                    sprintf("%s (DateTimeInterface or IntlDateFormatter)", VariableType::OBJECT),
                ],
            ]
        );
    }

    /**
     * @param string|int|float|array|DateTimeInterface|IntlDateFormatter $value
     *
     * @param null                                                       $context
     *
     * @return bool
     */
    public function isValid($value, $context = null): bool
    {
        $method = sprintf('validation%s', ucfirst(gettype($value)));
        if (false === method_exists($this, $method)) {
            $this->error(self::E_INVALID_TYPE);

            return false;
        }

        $this->setValue($value);
        if (false === $this->$method($value)) {
            $this->error(self::E_INVALID_DATE);

            return false;
        }

        return true;
    }

    private function validationString(string $value): bool
    {
        if ($this->format) {
            $value = $this->isISOFormat($this->format)
                ? $this->createDateTimeImmutableFromISO($value)
                : DateTime::createFromFormat($this->format, $value);
        } else {
            $value = date_create($value);
        }
        // Invalid dates can only manifest itself in the form of warnings (i.e. "2007-02-99")
        if (0 === DateTime::getLastErrors()['warning_count'] && (bool)$value) {
            return true;
        }
        if ($this->format) {
            $this->error(self::E_FALSE_FORMAT);
        }

        return false;
    }

    /** @noinspection PhpUnusedPrivateMethodInspection */
    private function validationArray(array $value): bool
    {
        return $this->validationString($this->convertArrayToDateString($value));
    }

    /** @noinspection PhpUnusedPrivateMethodInspection */
    private function validationInteger(int $value): bool
    {
        return (bool)date_create(sprintf("@%d", $value));
    }

    /** @noinspection PhpUnusedPrivateMethodInspection */
    private function validationDouble(float $value): bool
    {
        return (bool)DateTime::createFromFormat('U', $value);
    }

    /** @noinspection PhpUnusedPrivateMethodInspection */
    private function validationObject(object $value): bool
    {
        return ($value instanceof DateTimeInterface || $value instanceof IntlDateFormatter);
    }
}
