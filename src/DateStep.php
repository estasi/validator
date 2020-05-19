<?php

declare(strict_types=1);

namespace Estasi\Validator;

use DateInterval;
use DateTime;
use DateTimeImmutable;
use DateTimeInterface;
use Ds\Map;
use Estasi\Utility\Interfaces\VariableType;
use Exception;
use InvalidArgumentException;

use function boolval;
use function date_create_immutable;
use function gettype;
use function is_null;
use function is_string;
use function sprintf;
use function strncmp;
use function strtoupper;

/**
 * Class DateStep
 *
 * @package Estasi\Validator
 */
final class DateStep extends Abstracts\Validator
{
    use Traits\ConvertArrayToDateString;
    use Traits\ISODate;

    // names of constructor parameters to create via the factory
    public const OPT_FORMAT     = 'format';
    public const OPT_STEP       = 'step';
    public const OPT_START_DATE = 'startDate';
    // default values for constructor parameters
    public const STEP_ONE_DAY                = 'P1D';
    public const BASE_DATE_UNIX_EPOCH        = '1970-01-01';
    public const WITHOUT_FORMAT_CHECKED_DATE = null;
    // errors codes
    public const E_NOT_STEP = 'eNotStep';

    private const YEARS   = 'y';
    private const MONTHS  = 'm';
    private const DAYS    = 'd';
    private const HOURS   = 'h';
    private const MINUTES = 'i';
    private const SECONDS = 's';

    private DateInterval      $step;
    private DateTimeImmutable $startDate;
    private ?string           $format;
    private Date              $dateValidator;
    private Map               $stepIntervals;
    private Map               $formatsDateInterval;

    /**
     * DateStep constructor.
     *
     * @param string|DateInterval          $step      Takes as an argument a string in the format strtotime() or an
     *                                                object of the class \DateInterval
     * @param string|DateTimeInterface     $startDate the base date from which the step is counted
     * @param string|null                  $format    format of the date to be checked for validity
     * @param iterable<string, mixed>|null $options   Secondary validator options, such as the Translator, the length
     *                                                of the error message, hiding the value being checked, defining
     *                                                your own error messages, and so on.
     *
     * @throws \Exception when the step cannot be parsed as an interval
     * @throws \InvalidArgumentException
     */
    public function __construct(
        $step = self::STEP_ONE_DAY,
        $startDate = self::BASE_DATE_UNIX_EPOCH,
        ?string $format = self::WITHOUT_FORMAT_CHECKED_DATE,
        iterable $options = null
    ) {
        $this->formatsDateInterval = new Map(
            [
                self::YEARS   => 'year',
                self::MONTHS  => 'month',
                self::DAYS    => 'day',
                self::HOURS   => 'hour',
                self::MINUTES => 'minute',
                self::SECONDS => 'second',
            ]
        );
        $this->dateValidator       = new Date($format, $options);
        $this->step                = $this->createDateInterval($step);
        $this->stepIntervals       = $this->explodeDateInterval($this->step)
                                          ->filter();
        $this->format              = $format;
        /** @noinspection PhpFieldAssignmentTypeMismatchInspection */
        $this->startDate = $this->createDateTimeImmutable($startDate, true);

        parent::__construct(...$this->getValidOptionsForParent($options));
        $this->initErrorMessagesTemplates(
            [self::E_NOT_STEP => 'The checked date "%value%" does not match the specified step "%step%" from the initial date "%startDate%"!']
        );
        $this->initErrorMessagesVars(
            [
                self::OPT_STEP       => $this->stepIntervals->reduce([$this, 'getStepAsString']),
                self::OPT_START_DATE => $this->startDate->format(DateTimeInterface::W3C),
            ]
        );
    }

    /**
     * @inheritDoc
     */
    public function isValid($value, $context = null): bool
    {
        $checkDate = $this->createDateTimeImmutable($value);
        if (is_null($checkDate)) {
            $this->setErrors($this->dateValidator->getLastErrors());

            return false;
        }

        // if the checked date and the base date are correct, then we interrupt the function
        /** @noinspection PhpNonStrictObjectEqualityInspection */
        if ($checkDate == $this->startDate) {
            return true;
        }

        if ($this->isValidIfIssetOneInterval($checkDate)
            || $this->isValidIfStepNotContainYearOrMonth($checkDate)
            || $this->isValidIfStepContainYearOrMonth($checkDate)) {
            return true;
        }
        $this->error(self::E_NOT_STEP, [self::MESSAGE_VAR_VALUE => $value]);

        return false;
    }

    /**
     * Returns the specified step as a string
     * used for output to an error message
     *
     * @param string|null $carry
     * @param string      $format
     * @param int|float   $value
     *
     * @return string
     * @example P35DT5H -> 35 days 5 hours
     *
     */
    public function getStepAsString(?string $carry, string $format, $value): string
    {
        if (boolval($carry)) {
            $carry .= ' ';
        }
        $description = $this->formatsDateInterval->get($format) . ($value > 1 ? 's' : '');

        return sprintf('%s%s %s', $carry, $value, $description);
    }

    /**
     * @param string|DateInterval $step
     *
     * @return \DateInterval
     * @throws \Exception
     * @throws \InvalidArgumentException
     */
    private function createDateInterval($step): DateInterval
    {
        if (is_string($step)) {
            try {
                $step = strncmp($step, 'P', 1) ? DateInterval::createFromDateString($step) : new DateInterval($step);
            } catch (Exception $exception) {
                throw $exception;
            }
        }

        if ($step instanceof DateInterval) {
            return $step;
        }

        throw new InvalidArgumentException(
            sprintf(
                'Invalid step argument type. Expected string or object instance of the \DateInterval class; received %s!',
                $this->getReceivedType($step)
            )
        );
    }

    /**
     * Returns a valid list of formats for the \DateInterval object
     *
     * @param \DateInterval $dateInterval
     *
     * @return \Ds\Map
     */
    private function explodeDateInterval(DateInterval $dateInterval): Map
    {
        $map = new Map();
        foreach ($dateInterval as $format => $value) {
            if ($this->formatsDateInterval->hasKey($format)) {
                $map->put($format, $value);
            }
        }

        return $map;
    }

    /**
     * @param string|int|float|array<string|int|float>|DateTimeInterface $date
     * @param bool                                                       $throw
     *
     * @return \DateTimeImmutable|null
     */
    private function createDateTimeImmutable($date, bool $throw = false): ?DateTimeImmutable
    {
        if (false === $this->dateValidator->isValid($date)) {
            if ($throw) {
                throw new InvalidArgumentException($this->dateValidator->getLastErrorMessage());
            }

            return null;
        }

        if ($date instanceof DateTimeInterface) {
            if ($date instanceof DateTime) {
                return DateTimeImmutable::createFromMutable($date);
            }

            /** @noinspection PhpIncompatibleReturnTypeInspection */
            return $date;
        }
        switch (gettype($date)) {
            case VariableType::INTEGER:
                return date_create_immutable(sprintf('@%d', $date));
            case VariableType::DOUBLE:
                return DateTimeImmutable::createFromFormat('U', $date);
            /** @noinspection PhpMissingBreakStatementInspection */
            case VariableType::ARRAY:
                $date = $this->convertArrayToDateString($date);
            // no break;
            case VariableType::STRING:
                if (isset($this->format)) {
                    return $this->isISOFormat($this->format)
                        ? $this->createDateTimeImmutableFromISO($date)
                        : DateTimeImmutable::createFromFormat($this->format, $date);
                } else {
                    return date_create_immutable($date);
                }
            default:
                return null;
        }
    }

    private function isValidIfIssetOneInterval(DateTimeImmutable $checkDate): bool
    {
        if ($this->isNumberIntervalsGreaterThanOne()) {
            return false;
        }
        // get interval name: y, m, d, h, i, s; and interval step
        ['key' => $interval, 'value' => $step] = $this->stepIntervals->first()
                                                                     ->toArray();
        // We get the absolute time difference between the checked date and the base date
        $dateDiff = $checkDate->diff($this->startDate, true);
        $totalD   = (int)$dateDiff->format('%a');
        // retrieves the intervals (y, m, d, h, i, s) specified in the verification step
        $dateIntervals = $this->explodeDateInterval($dateDiff);
        // if there was a daylight saving time transition
        if (self::DAYS === $interval
            && $checkDate->format('H:i:s') === $this->startDate->format('H:i:s')
            && $dateIntervals->get(self::HOURS) === 23) {
            $dateIntervals->put(self::HOURS, 0);
        }
        // getting the name of the method for checking the interval
        $method = sprintf('isValidInterval%s', strtoupper($interval));

        return $this->{$method}($step, $dateIntervals, $totalD);
    }

    private function isValidIfStepNotContainYearOrMonth(DateTimeImmutable $checkDate)
    {
        if (false === $this->isNumberIntervalsGreaterThanOne() || $this->hasStepIntervalsYearOrMonth()) {
            return false;
        }

        $secondsPerMinute = 60;
        $secondsPerHour   = $secondsPerMinute * $secondsPerMinute;
        $stepInSeconds    = $this->stepIntervals->get(self::DAYS, 0) * 24 * $secondsPerHour
                            + $this->stepIntervals->get(self::HOURS, 0) * $secondsPerHour
                            + $this->stepIntervals->get(self::MINUTES, 0) * $secondsPerMinute
                            + $this->stepIntervals->get(self::SECONDS, 0);
        if (0 === $stepInSeconds) {
            return false;
        }

        return (new Step($stepInSeconds, $this->startDate->getTimestamp()))->isValid($checkDate->getTimestamp());
    }

    private function isValidIfStepContainYearOrMonth(DateTimeImmutable $checkDate): bool
    {
        if (false === $this->isNumberIntervalsGreaterThanOne() || false === $this->hasStepIntervalsYearOrMonth()) {
            return false;
        }

        if ($this->startDate < $checkDate) {
            $startDate = clone $this->startDate;
            while ($startDate < $checkDate) {
                $startDate = $startDate->add($this->step);
                /** @noinspection PhpNonStrictObjectEqualityInspection */
                if ($startDate == $checkDate) {
                    return true;
                }
            }
        } elseif ($this->startDate > $checkDate) {
            $startDate = clone $this->startDate;
            while ($startDate > $checkDate) {
                $startDate = $startDate->sub($this->step);
                if ($startDate == $checkDate) {
                    return true;
                }
            }
        }

        return false;
    }

    /** @noinspection PhpUnusedPrivateMethodInspection */
    private function isValidIntervalY(int $step, Map $dateIntervals): bool
    {
        $isEmptyIntervalsAfterY = $dateIntervals->slice(1)
                                                ->filter()
                                                ->isEmpty();

        return $step > 1
            ? $isEmptyIntervalsAfterY && $this->isValidStep($step, $dateIntervals->get(self::YEARS))
            : $isEmptyIntervalsAfterY;
    }

    /** @noinspection PhpUnusedPrivateMethodInspection */
    private function isValidIntervalM(int $step, Map $dateIntervals): bool
    {
        $isEmptyIntervalsAfterM = $dateIntervals->slice(2)
                                                ->filter()
                                                ->isEmpty();
        $totalM                 = $dateIntervals->get(self::YEARS, 0) * 12
                                  + $dateIntervals->get(self::MONTHS);

        return $step > 1
            ? $isEmptyIntervalsAfterM && $this->isValidStep($step, $totalM)
            : $isEmptyIntervalsAfterM;
    }

    /** @noinspection PhpUnusedPrivateMethodInspection */
    private function isValidIntervalD(int $step, Map $dateIntervals, int $totalD): bool
    {
        $isEmptyIntervalsAfterD = $dateIntervals->slice(3)
                                                ->filter()
                                                ->isEmpty();

        return $step > 1 ? $isEmptyIntervalsAfterD && $this->isValidStep($step, $totalD) : $isEmptyIntervalsAfterD;
    }

    /** @noinspection PhpUnusedPrivateMethodInspection */
    private function isValidIntervalH(int $step, Map $dateIntervals): bool
    {
        $isEmptyIntervalsAfterH = $dateIntervals->slice(4)
                                                ->filter()
                                                ->isEmpty();

        return $step > 1
            ? $isEmptyIntervalsAfterH && $this->isValidStep($step, $dateIntervals->get(self::HOURS))
            : $isEmptyIntervalsAfterH;
    }

    /** @noinspection PhpUnusedPrivateMethodInspection */
    private function isValidIntervalI(int $step, Map $dateIntervals): bool
    {
        $isEmptyIntervalsAfterI = $dateIntervals->slice(5)
                                                ->filter()
                                                ->isEmpty();
        $totalI                 = $dateIntervals->get(self::HOURS, 0) * 60
                                  + $dateIntervals->get(self::MINUTES);

        return $step > 1 ? $isEmptyIntervalsAfterI && $this->isValidStep($step, $totalI) : $isEmptyIntervalsAfterI;
    }

    /** @noinspection PhpUnusedPrivateMethodInspection */
    private function isValidIntervalS(int $step, Map $dateIntervals): bool
    {
        $totalS = $dateIntervals->get(self::HOURS, 0) * 3600
                  + $dateIntervals->get(self::MINUTES, 0) * 60
                  + $dateIntervals->get(self::SECONDS);

        return $this->isValidStep($step, $totalS);
    }

    private function isNumberIntervalsGreaterThanOne(): bool
    {
        return $this->stepIntervals->count() > 1;
    }

    private function hasStepIntervalsYearOrMonth(): bool
    {
        return $this->stepIntervals->hasKey(self::YEARS) || $this->stepIntervals->hasKey(self::MONTHS);
    }

    private function isValidStep($step, $value): bool
    {
        return (new Step($step))->isValid($value);
    }
}
