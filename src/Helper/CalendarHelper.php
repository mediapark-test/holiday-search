<?php

namespace App\Helper;

use App\Repository\HolidayRepository;
use App\Service\APIClientService;

class CalendarHelper
{

    const WORKDAY_STATUS = 'WorkDay';
    const FREE_DAY_STATUS = 'Holiday';
    const HOLIDAY_STATUS = 'Free day';

    public function holidaysIndexed($holidays): array
    {
        $indexedHolidays = [];
        foreach ($holidays as $holiday) {
            $indexedHolidays[$holiday->getDate()->format(APIClientService::DATE_FORMAT)] = $holiday;
        }
        return $indexedHolidays;
    }

    public function holidaysGrouped($holidays): array
    {

        $groupedHolidays = $this->getGroupedMonth();

        $date = new \DateTimeImmutable();
        foreach (range(1, 12) as $month) {
            $monthDate = $date->setDate(1, $month, 1);
            $groupedHolidays[$monthDate->format('m')]['name'] = $monthDate->format('F');
        }


        foreach ($holidays as $dateIndex => $holiday) {
            if ($holiday->getType() == HolidayRepository::HOLIDAY_TYPE) {
                $groupedHolidays[$holiday->getDate()->format('m')]['holidays'][$holiday->getDate()->format(APIClientService::DATE_FORMAT)] = $holiday;
            }
        }
        return $groupedHolidays;
    }

    public function getGroupedMonth(): array
    {

        $groupedHolidays = [];

        $date = new \DateTimeImmutable();
        foreach (range(1, 12) as $month) {
            $monthDate = $date->setDate(1, $month, 1);
            $groupedHolidays[$monthDate->format('m')]['name'] = $monthDate->format('F');
        }

        return $groupedHolidays;
    }

    public function currentDayStatus($holidays, $yearNum): string
    {
        $status = self::WORKDAY_STATUS;
        $date = new \DateTime();
        $date->setDate($yearNum, $date->format('m'), $date->format('d'));
        if (isset($holidays[$date->format(APIClientService::DATE_FORMAT)])) {
            $holiday = $holidays[$date->format(APIClientService::DATE_FORMAT)];
            $status = $holiday->getType() == HolidayRepository::HOLIDAY_TYPE ? self::HOLIDAY_STATUS : self::FREE_DAY_STATUS;
        }
        return $status;
    }

    public function maxFreeDays($holidays): int
    {
        $counter = 1;
        $max = 1;
        foreach ($holidays as $holiday) {
            $holidayNextDate = \DateTimeImmutable::createFromMutable($holiday->getDate())->modify('+1 day')->format(APIClientService::DATE_FORMAT);
            if (isset($holidays[$holidayNextDate])) {
                $counter++;
                if ($counter > $max) {
                    $max = $counter;
                }
            } else {
                $counter = 1;
            }
        }

        return $max;
    }

}