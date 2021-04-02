<?php

namespace App\Service;

use Symfony\Contracts\HttpClient\HttpClientInterface;

class APIClientService
{

    const API_HOST = 'https://kayaposoft.com/enrico';
    const API_VERSION = 'json/v2.0';

    const DATE_FORMAT = 'd-m-Y';
    private $client;

    public function __construct(HttpClientInterface $client)
    {
        $this->client = $client;
    }

    /**
     * @return array
     */
    public function getCounties(): array
    {
        return $this->fetchAPIRequest('getSupportedCountries');
    }

    public function checkWorkDay($countryCode, $date)
    {
        $params = [
            'date' => $date,
            'country' => $countryCode,
        ];
        $response = $this->fetchAPIRequest('isWorkDay', $params);

        return $response['isWorkDay'];
    }

    /**
     * @param $countryCode
     * @param $yearNum
     * @return array
     */
    public function getHoliday($countryCode, $yearNum): array
    {
        $params = [
            'year' => $yearNum,
            'country' => $countryCode,
        ];
        return $this->fetchAPIRequest('getHolidaysForYear', $params);
    }

    /**
     * @param $countryCode
     * @param $yearNum
     * @return array
     */
    public function getHolidays(string $countryCode, string $yearNum): array
    {
        $params = [
            'country' => $countryCode,
            'year' => $yearNum,
        ];
        return $this->fetchAPIRequest('getHolidaysForYear', $params);
    }

    protected function fetchAPIRequest($action, $params = []): array
    {

        $url = sprintf('%s/%s/?action=%s', self::API_HOST, self::API_VERSION, $action);
        if (!empty($params)) {
            $url = sprintf('%s&%s', $url, http_build_query($params));
        }

        $response = $this->client->request(
            'GET',
            $url
        );

        return $response->toArray();
    }

}