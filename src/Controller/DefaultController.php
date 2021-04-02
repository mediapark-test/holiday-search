<?php

namespace App\Controller;

use App\Entity\Country;
use App\Entity\Holiday;
use App\Helper\CalendarHelper;
use App\Repository\HolidayRepository;
use App\Service\DBDataService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class DefaultController extends AbstractController
{

    const AVAILABLE_YEARS_COUNT = 5;

    /**
     * @Route("/", name="index")
     * @param DBDataService $dbData
     * @return Response
     */
    public function index(DBDataService $dbData): Response
    {


        $countries = $dbData->getCounties();

        return $this->render('home/index.html.twig', [
            'searchParams' => [
                'countryCode' => '',
                'year' => 0,
            ],
            'countries' => $countries,
        ]);
    }

    /**
     * @Route("/{code}/{yearNum}", name="search")
     */
    public function search(Country $country, string $yearNum, DBDataService $dbData, CalendarHelper $calendarHelper): Response
    {

        $date = new \DateTimeImmutable();
        if (!is_numeric($yearNum) || !in_array($yearNum, range($date->format('Y'), $date->modify('+' . self::AVAILABLE_YEARS_COUNT . ' year')->format('Y')))) {
            throw $this->createNotFoundException('The year param not valid');
        }

        $countries = $dbData->getCounties();

        $results = [];

        $holidays = $dbData->getHoliday($country->getCode(), $yearNum);
        if (empty($holidays)) {
            $results['holidays_message'] = 'Holidays are not loaded yet, please reload you search page!';
        } else {
            $indexedHolidays = $calendarHelper->holidaysIndexed($holidays);
            $results['totalHolidays'] = count(array_filter($indexedHolidays, function (Holiday $holiday) {
                return $holiday->getType() == HolidayRepository::HOLIDAY_TYPE;
            }));
            if ($results['totalHolidays'] != count($indexedHolidays)) {
                $results['currentDayStatus'] = $calendarHelper->currentDayStatus($indexedHolidays, $yearNum);
                $results['maxFreeDays'] = $calendarHelper->maxFreeDays($indexedHolidays);
            } else {
                $results['currentDayStatus'] = 'Work days are not loaded yet, please reload you search page!';
                $results['maxFreeDays'] = 'Work days are not loaded yet, please reload you search page!';
            }
            $results['holidaysByMonth'] = $calendarHelper->holidaysGrouped($indexedHolidays);
        }

        return $this->render('home/index.html.twig', [
            'searchParams' => [
                'countryCode' => $country->getCode(),
                'year' => $yearNum,
            ],
            'results' => $results,
            'countries' => $countries,
        ]);
    }

}