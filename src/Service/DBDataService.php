<?php

namespace App\Service;


use App\Entity\Country;
use App\Entity\Holiday;
use App\Entity\Task;
use App\Helper\CalendarHelper;
use App\Repository\CountryRepository;
use App\Repository\HolidayRepository;
use App\Repository\TaskRepository;
use Doctrine\ORM\EntityManagerInterface;

class DBDataService
{
    /**
     * @var APIClientService
     */
    protected APIClientService $apiService;
    /**
     * @var EntityManagerInterface
     */
    protected EntityManagerInterface $em;
    /**
     * @var CountryRepository
     */
    protected CountryRepository $countryRepository;
    /**
     * @var HolidayRepository
     */
    protected HolidayRepository $holidayRepository;
    /**
     * @var CalendarHelper
     */
    protected CalendarHelper $calendarHelper;
    /**
     * @var \OldSound\RabbitMqBundle\RabbitMq\Producer
     */
    protected \OldSound\RabbitMqBundle\RabbitMq\Producer $holidayQueue;
    /**
     * @var \OldSound\RabbitMqBundle\RabbitMq\Producer
     */
    protected \OldSound\RabbitMqBundle\RabbitMq\Producer $freeDayQueue;

    /**
     * DBDataService constructor.
     * @param APIClientService $apiService
     * @param EntityManagerInterface $em
     * @param CountryRepository $countryRepository
     * @param HolidayRepository $holidayRepository
     * @param \OldSound\RabbitMqBundle\RabbitMq\Producer $freeDayProducer
     * @param CalendarHelper $calendarHelper
     */
    public function __construct(
        APIClientService $apiService,
        EntityManagerInterface $em,
        CountryRepository $countryRepository,
        HolidayRepository $holidayRepository,
        \OldSound\RabbitMqBundle\RabbitMq\Producer $freeDayProducer,
        CalendarHelper $calendarHelper)
    {
        $this->apiService = $apiService;
        $this->countryRepository = $countryRepository;
        $this->holidayRepository = $holidayRepository;

        $this->freeDayQueue = $freeDayProducer;
        $this->calendarHelper = $calendarHelper;
    }

    /**
     * @return array
     */
    public function getCounties(): array
    {

        $db_countries = $this->countryRepository->findAll();
        return $db_countries;
    }

    /**
     * @param $countryCode
     * @param $yearNum
     * @return array
     */
    public function getHoliday($countryCode, $yearNum): array
    {

        $dbHolidays = $this->holidayRepository->search($countryCode, $yearNum);
        if (empty($dbHolidays)) {
            $dbHolidays = $this->setHoliday($countryCode, $yearNum);
        }
        return $dbHolidays;
    }

    /**
     * @param $holidays
     * @param $countryCode
     * @param $yearNum
     * @return void
     */
    public function getFreeDay($holidays, $countryCode, $yearNum): void
    {
        $date = new \DateTimeImmutable();
        $startDate = $date->setDate($yearNum, 1, 1);
        $endDate = $date->setDate($yearNum, 12, 31);
        foreach (new \DatePeriod($startDate, new \DateInterval('P1D'), $endDate) as $date) {
            if (!array_key_exists($date->format(APIClientService::DATE_FORMAT), $holidays)) {
                $searchParams = ["country" => $countryCode, "date" => $date->format(APIClientService::DATE_FORMAT)];
                $this->freeDayQueue->publish(json_encode($searchParams));
            }
        }

    }

    public function setHoliday($countryCode, $yearNum)
    {

        $date = new \DateTimeImmutable();
        $apiHolidays = $this->apiService->getHolidays($countryCode, $yearNum);
        $holidays = [];
        foreach ($apiHolidays as $apiHoliday) {
            $holidayDate = $date->setDate($apiHoliday['date']['year'], $apiHoliday['date']['month'], $apiHoliday['date']['day']);
            $holidays[$holidayDate->format(APIClientService::DATE_FORMAT)] = $apiHoliday;
        }

        $this->getFreeDay($holidays, $countryCode, $yearNum);

        return $this->holidayRepository->createAll($holidays, $countryCode);
    }

}