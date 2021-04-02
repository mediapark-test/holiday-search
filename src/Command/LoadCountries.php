<?php

namespace App\Command;

use App\Entity\Holiday;
use App\Repository\CountryRepository;
use App\Repository\TaskRepository;
use App\Service\APIClientService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class LoadCountries extends Command
{
    protected static $defaultName = 'app:load-counties';
    /**
     * @var APIClientService
     */
    protected APIClientService $apiService;
    /**
     * @var CountryRepository
     */
    protected CountryRepository $countryRepository;

    public function __construct(
        string $name = null, CountryRepository $countryRepository, APIClientService $apiService)
    {

        $this->apiService = $apiService;
        $this->countryRepository = $countryRepository;
        parent::__construct($name);
    }

    protected function configure()
    {

    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {

        $api_countries = $this->apiService->getCounties();
        $this->countryRepository->createAll($api_countries);
        return Command::SUCCESS;
    }
}