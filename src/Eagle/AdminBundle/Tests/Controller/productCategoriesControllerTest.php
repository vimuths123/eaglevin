<?php

namespace Eagle\AdminBundle\Tests\Controller;

use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

class productCategoriesControllerTest extends WebTestCase
{
    public function testAdd()
    {
        $client = static::createClient();

        $crawler = $client->request('GET', 'productCategories/add');
    }

    public function testView()
    {
        $client = static::createClient();

        $crawler = $client->request('GET', 'productCategories/view');
    }

}
