<?php

declare(strict_types=1);

namespace Estasi\Validator\Traits;

use DateTimeImmutable;

use function preg_match;

/**
 * Trait ISODate
 *
 * @package Estasi\Validator\Traits
 */
trait ISODate
{
    /**
     * Returns true if the format is written as "year and week in ISO format" and "Year, week in ISO format and day of
     * the week", otherwise returns false
     *
     * @link https://www.php.net/manual/en/datetime.formats.compound.php
     *
     * @param string $format
     *
     * @return bool
     */
    private function isISOFormat(string $format): bool
    {
        return (bool)preg_match('`^Y{1,2}\x2D?\x5CWW`', $format);
    }

    /**
     * Returns the \DateTimeImmutable object created from ISO format or FALSE if an error occurs
     *
     * @param string $date
     *
     * @return \DateTimeImmutable|false
     */
    private function createDateTimeImmutableFromISO(string $date)
    {
        preg_match(
            '`(?P<year>\d{2,4})\x2D?W(?P<week>0[1-9]|[1-4][0-9]|5[0-3])(?:\x2D?(?P<day>[0-7]))?`',
            $date,
            $iso
        );

        return (new DateTimeImmutable())->setISODate((int)$iso['year'], (int)$iso['week'], (int)($iso['day'] ?? 1));
    }
}
