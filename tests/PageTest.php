<?php

namespace App\Tests;

use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

class PageTest extends WebTestCase
{
    public function testIndex(): void
    {
        $client = static::createClient();
        $crawler = $client->request('GET', '/');

        $this->assertResponseIsSuccessful();
    }

    public function testIndexContent(): void
    {
        $client = static::createClient();
        $crawler = $client->request('GET', '/');

        $this->assertSelectorTextContains('#form-search', 'Search');
    }

    public function testIndexContentResults(): void
    {
        $client = static::createClient();
        $crawler = $client->request('GET', '/');

        $this->assertSelectorNotExists('#results');
    }

    public function testSearchContent(): void
    {
        $client = static::createClient();
        $crawler = $client->request('GET', '/usa/2024');
        $this->assertSelectorExists('#results');
        $this->assertSelectorTextContains('#results', 'Total amount of a public holidays');
    }

    public function testSearchUrlValidation(): void
    {
        $client = static::createClient();
        $crawler = $client->request('GET', '/2024/usa');
        $response = $client->getResponse();
        $this->assertEquals(404, $response->getStatusCode());
    }

}
