<?php

namespace Eagle\ShopBundle\Tests\Controller;

use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

class GlobalControllerTest extends WebTestCase
{
    public function testIndex()
    {
        $client = static::createClient();

        $crawler = $client->request('GET', '/index');
    }

    public function testStores()
    {
        $client = static::createClient();

        $crawler = $client->request('GET', '/stores');
    }

    public function testProducts()
    {
        $client = static::createClient();

        $crawler = $client->request('GET', '/products');
    }

}
