<?php

namespace Eagle\AdminBundle\Tests\Controller;

use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

class productsControllerTest extends WebTestCase
{
    public function testAdd()
    {
        $client = static::createClient();

        $crawler = $client->request('GET', 'products/add');
    }

    public function testView()
    {
        $client = static::createClient();

        $crawler = $client->request('GET', 'products/view');
    }

}
