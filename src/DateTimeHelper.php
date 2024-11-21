<?php
/*********************************************************************
 * This Code is licensed under the GPL-3.0 License and is Part of a
 * ILIAS Plugin developed by sr solutions ag in Switzerland.
 *
 * https://sr.solutions
 *
 *********************************************************************/

declare(strict_types=1);

namespace srag\Plugins\SrLifeCycleManager;

use DateTimeImmutable;
use LogicException;
use Sabre\VObject\Property\VCard\Date;

/**
 * This trait is responsible for managing datetime objects.
 *
 * @author Thibeau Fuhrer <thibeau@sr.solutions>
 *
 * It was introduced to centralize the management of datetime objects and
 * to unify a default format for date's and datetime.
 */
trait DateTimeHelper
{
    /**
     * @var string default mysql datetime format
     */
    private static $mysql_datetime_format = 'Y-m-d H:i:s';

    /**
     * @var string default mysql date format
     */
    private static $mysql_date_format = 'Y-m-d';

    /**
     * @var string datetime format for presentation
     */
    private static $pretty_datetime_format = 'd.m.Y';

    /**
     * @var string date format for presentation
     */
    private static $pretty_date_format = 'd.m.Y';

    /**
     * @param string $datetime (Y-m-d H:i:s)
     * @return DateTimeImmutable|null
     */
    protected function getDateTime(string $datetime): ?DateTimeImmutable
    {
        return (DateTimeImmutable::createFromFormat(
            self::$mysql_datetime_format,
            $datetime
        )) ?: null;
    }

    /**
     * @param string $date (Y-m-d)
     * @return DateTimeImmutable|null
     */
    protected function getDate(string $date): ?DateTimeImmutable
    {
        return (DateTimeImmutable::createFromFormat(
            self::$mysql_datetime_format,
            "$date 00:00:00"
        )) ?: null;
    }

    /**
     * @param array<string, string> $query_result
     * @param string                $column_name
     * @return DateTimeImmutable
     */
    protected function getDateTimeByQueryResult(array $query_result, string $column_name): ?DateTimeImmutable
    {
        if (isset($query_result[$column_name])) {
            return $this->getRequiredDateTimeByQueryResult($query_result, $column_name);
        }

        return null;
    }

    /**
     * @param array<string, string> $query_result
     * @param string                $column_name
     * @return DateTimeImmutable
     */
    protected function getRequiredDateTimeByQueryResult(array $query_result, string $column_name): DateTimeImmutable
    {
        if (!isset($query_result[$column_name])) {
            throw new LogicException("Retrieved inconsistent mysql data, missing '$column_name'.");
        }

        $dateTime = $this->getDateTime($query_result[$column_name]);
        if (null === $dateTime) {
            throw new LogicException("Could not create datetime object from mysql format.");
        }

        return $dateTime;
    }

    /**
     * @param array<string, string> $query_result
     * @param string                $column_name
     * @return DateTimeImmutable
     */
    protected function getDateByQueryResult(array $query_result, string $column_name): ?DateTimeImmutable
    {
        if (isset($query_result[$column_name])) {
            return $this->getRequiredDateByQueryResult($query_result, $column_name);
        }

        return null;
    }

    /**
     * @param array<string, string> $query_result
     * @param string                $column_name
     * @return DateTimeImmutable
     */
    protected function getRequiredDateByQueryResult(array $query_result, string $column_name): DateTimeImmutable
    {
        if (!isset($query_result[$column_name])) {
            throw new LogicException("Retrieved inconsistent mysql data, missing '$column_name'.");
        }

        $date = $this->getDate($query_result[$column_name]);
        if (null === $date) {
            throw new LogicException("Could not create datetime object from mysql format.");
        }

        return $date;
    }

    /**
     * Returns the amount of days between $before and $after. Note that negative
     * numbers are returned if $before is past $after.
     *
     * @param DateTimeImmutable $before
     * @param DateTimeImmutable $after
     * @return int
     */
    protected function getGap(DateTimeImmutable $before, DateTimeImmutable $after): int
    {
        // set each datetime object's H:i:s to 00:00:00 in order to
        // return an accurate gap.
        $comparable_before = ($before->setTime(0, 0, 0)) ?: $before;
        $comparable_after = ($after->setTime(0, 0, 0)) ?: $after;

        return (int) $comparable_before->diff($comparable_after)->format("%r%a");
    }

    /**
     * Returns a datetime object so far in the future it will most likely never
     * be reached.
     *
     * @return DateTimeImmutable
     */
    protected function getUnreachableDate(): DateTimeImmutable
    {
        $unreachable = $this->getDateTime("9999-12-31 59:59:59");
        if (null === $unreachable) {
            throw new LogicException("Could not create datetime object for unreachable date.");
        }

        return $unreachable;
    }

    /**
     * @return DateTimeImmutable
     */
    protected function getCurrentDate(): DateTimeImmutable
    {
        $today = $this->getDate(date(self::$mysql_date_format));
        if (null === $today) {
            throw new LogicException("Could not create datetime object for today's date.");
        }

        return $today;
    }

    /**
     * @param DateTimeImmutable $datetime
     * @return string
     */
    protected function getMysqlDateTimeString(DateTimeImmutable $datetime): string
    {
        return $datetime->format(self::$mysql_datetime_format);
    }

    /**
     * @param DateTimeImmutable $date
     * @return string
     */
    protected function getMysqlDateString(DateTimeImmutable $date): string
    {
        return $date->format(self::$mysql_date_format);
    }

    /**
     * @param DateTimeImmutable $datetime
     * @return string
     */
    protected function getPrettyDateTimeString(DateTimeImmutable $datetime): string
    {
        return $datetime->format(self::$pretty_datetime_format);
    }

    /**
     * @param DateTimeImmutable $date
     * @return string
     */
    protected function getPrettyDateString(DateTimeImmutable $date): string
    {
        return $date->format(self::$pretty_date_format);
    }
}
