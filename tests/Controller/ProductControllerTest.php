<?php

namespace App\Tests\Controller;

use Elastic\Elasticsearch\ClientBuilder;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\Cache\Adapter\RedisAdapter;

class ProductControllerTest extends WebTestCase
{
    public function testCreateProduct()
    {
        $client = static::createClient();
        $client->request('POST', '/api/products', [], [], ['CONTENT_TYPE' => 'application/json'], json_encode([
            'name' => 'Test Product',
            'category' => 'Test Category',
            'price' => 100.0,
            'stock' => 50,
        ]));

        $this->assertEquals(201, $client->getResponse()->getStatusCode(), $client->getResponse()->getContent());

        $clientBuilder = ClientBuilder::create();
        $clientBuilder->setHosts(['localhost:9200']);
        $client = $clientBuilder->build();

        $params = [
            'index' => 'products',
            'body'  => [
                'query' => [
                    'match' => [
                        'name' => 'Test Product'
                    ]
                ]
            ]
        ];
        $response = $client->search($params);
        $this->assertNotEmpty($response['hits']['hits'], 'Product not indexed in Elasticsearch');
    }

    public function testGetProduct()
    {
        $client = static::createClient();
        $client->request('GET', '/api/products');

        $this->assertEquals(200, $client->getResponse()->getStatusCode(), $client->getResponse()->getContent());
        $this->assertNotEmpty($client->getResponse()->getContent());
    }

    public function testSearchProduct()
    {
        $client = static::createClient();
        $client->request('GET', '/api/products/search?q=Test');

        $this->assertEquals(200, $client->getResponse()->getStatusCode(), $client->getResponse()->getContent());
        $this->assertNotEmpty($client->getResponse()->getContent());
    }

    public function testProductCache()
    {
        $client = static::createClient();
        $client->request('GET', '/api/products');

        $redis = RedisAdapter::createConnection('redis://127.0.0.1:6379');

        $keys = $redis->keys('*');
        $this->assertNotEmpty($keys, 'Redis cache is empty');
    }

    public function testGetProductDetails()
    {
        $client = static::createClient();
        $client->request('POST', '/api/products', [], [], ['CONTENT_TYPE' => 'application/json'], json_encode([
            'name' => 'Detail Product',
            'category' => 'Detail Category',
            'price' => 100.0,
            'stock' => 50,
        ]));

        $response = json_decode($client->getResponse()->getContent(), true);
        $productId = $response['id'];

        $client->request('GET', '/api/products/' . $productId);
        $this->assertEquals(200, $client->getResponse()->getStatusCode(), $client->getResponse()->getContent());
        $this->assertNotEmpty($client->getResponse()->getContent());
    }

    public function testCreateInvalidProduct()
    {
        $client = static::createClient();
        $client->request('POST', '/api/products', [], [], ['CONTENT_TYPE' => 'application/json'], json_encode([
            'name' => '',
            'category' => '',
            'price' => -100.0,
            'stock' => -50,
        ]));

        $this->assertEquals(400, $client->getResponse()->getStatusCode(), $client->getResponse()->getContent());
    }
}
