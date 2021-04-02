<?php

namespace App\Consumer;

use App\Entity\Holiday;
use App\Repository\HolidayRepository;
use App\Service\APIClientService;
use OldSound\RabbitMqBundle\RabbitMq\ConsumerInterface;
use PhpAmqpLib\Message\AMQPMessage;

/**
 * Class NotificationConsumer
 */
class FreeDayConsumer implements ConsumerInterface
{

    protected APIClientService $apiService;
    /**
     * @var HolidayRepository
     */
    protected HolidayRepository $holidayRepository;

    public function __construct(
        HolidayRepository $holidayRepository,
        APIClientService $apiService
    )
    {
        $this->apiService = $apiService;
        $this->holidayRepository = $holidayRepository;
    }

    /**
     * @return void
     * @var AMQPMessage $msg
     */
    public function execute(AMQPMessage $msg)
    {

        $searchParams = json_decode($msg->body, true);
        if (!$this->apiService->checkWorkDay($searchParams['country'], $searchParams['date'])) {

            $date = array_combine(['day', 'month', 'year'], explode('-', $searchParams['date']));
            $holiday = [
                'date' => $date,
                'name' => [],
            ];

            $this->holidayRepository->createAll([
                $holiday
            ], $searchParams['country']);
        }
        return ConsumerInterface::MSG_ACK_SENT;
    }
}
