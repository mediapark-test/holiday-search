<?php

namespace App\Repository;

use App\Entity\Holiday;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;
use Monolog\DateTimeImmutable;

/**
 * @method Holiday|null find($id, $lockMode = null, $lockVersion = null)
 * @method Holiday|null findOneBy(array $criteria, array $orderBy = null)
 * @method Holiday[]    findAll()
 * @method Holiday[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class HolidayRepository extends ServiceEntityRepository
{

    const FREE_DAY_TYPE = 'free_day';
    const HOLIDAY_TYPE = 'holiday';

    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Holiday::class);
    }


    public function search($countryCode, $yearNum)
    {
        return $this->createQueryBuilder('h')
            ->andWhere('h.country = :country')
            ->setParameter('country', $countryCode)
            ->andWhere('h.date BETWEEN :start AND :end')
            ->setParameter('start', $yearNum . '-01-01')
            ->setParameter('end', $yearNum . '-12-31')
            ->orderBy('h.date', 'ASC')
            ->getQuery()
            ->getResult();
    }

    public function createAll($holidays, $countryCode): array
    {

        $dbHolidays = [];
        foreach ($holidays as $holiday) {

            $name = '';
            foreach ($holiday['name'] as $langData) {
                if ($langData['lang'] == 'en') {
                    $name = $langData['text'];
                    break;
                }
            }

            $type = isset($holiday['holidayType']) ? self::HOLIDAY_TYPE : self::FREE_DAY_TYPE;

            $date = new \DateTime();

            $holidayEntity = new Holiday();
            $holidayEntity->setCountry($countryCode);
            $holidayEntity->setName($name);
            $holidayEntity->setDate($date->setDate($holiday['date']['year'], $holiday['date']['month'], $holiday['date']['day']));
            $holidayEntity->setType($type);

            $dbHolidays[$date->format('Y:m:d')] = $holidayEntity;
            $this->_em->persist($holidayEntity);
        }
        $this->_em->flush();
        $this->_em->clear();

        return $dbHolidays;
    }
}
