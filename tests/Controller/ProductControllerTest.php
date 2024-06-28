<?php

namespace App\Tests\Controller;

use Elastic\Elasticsearch\ClientBuilder;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\Cache\Adapter\RedisAdapter;

class ProductControllerTest extends WebTestCase
{
    private $client;

    protected function setUp(): void
    {
        $this->client = static::createClient();
    }

    private function createProduct(array $data): void
    {
        $this->client->request('POST', '/api/products', [], [], ['CONTENT_TYPE' => 'application/json'], json_encode($data));
    }

    public function testCreateProduct()
    {
        $this->createProduct([
            'name' => 'Test Product',
            'category' => 'Test Category',
            'price' => 100.0,
            'stock' => 50,
        ]);

        $this->assertEquals(201, $this->client->getResponse()->getStatusCode(), $this->client->getResponse()->getContent());
    }

    public function testGetProduct()
    {
        $this->client->request('GET', '/api/products');

        $this->assertEquals(200, $this->client->getResponse()->getStatusCode(), $this->client->getResponse()->getContent());
        $this->assertNotEmpty($this->client->getResponse()->getContent());
    }

    public function testElasticsearchIndexing()
    {
        $this->createProduct([
            'name' => 'Elasticsearch Product',
            'category' => 'Elasticsearch Category',
            'price' => 150.0,
            'stock' => 60,
        ]);

        $clientBuilder = ClientBuilder::create();
        $clientBuilder->setHosts(['localhost:9200']);
        $client = $clientBuilder->build();

        $params = [
            'index' => 'products',
            'body'  => [
                'query' => [
                    'match' => [
                        'name' => 'Elasticsearch Product'
                    ]
                ]
            ]
        ];
        $response = $client->search($params);
        $this->assertNotEmpty($response['hits']['hits'], 'Product not indexed in Elasticsearch');
    }

    public function testSearchProduct()
    {
        $this->client->request('GET', '/api/products/search?q=Test');

        $this->assertEquals(200, $this->client->getResponse()->getStatusCode(), $this->client->getResponse()->getContent());
        $this->assertNotEmpty($this->client->getResponse()->getContent());
    }

    public function testProductCache()
    {
        $this->client->request('GET', '/api/products');

        $redis = RedisAdapter::createConnection('redis://127.0.0.1:6379');

        $keys = $redis->keys('*');
        $this->assertNotEmpty($keys, 'Redis cache is empty');
    }

    public function testGetProductDetails()
    {
        $this->createProduct([
            'name' => 'Detail Product',
            'category' => 'Detail Category',
            'price' => 100.0,
            'stock' => 50,
        ]);

        $response = json_decode($this->client->getResponse()->getContent(), true);
        $productId = $response['id'];

        $this->client->request('GET', '/api/products/' . $productId);
        $this->assertEquals(200, $this->client->getResponse()->getStatusCode(), $this->client->getResponse()->getContent());
        $this->assertNotEmpty($this->client->getResponse()->getContent());
    }

    public function testCreateInvalidProduct()
    {
        $this->createProduct([
            'name' => '',
            'category' => '',
            'price' => -100.0,
            'stock' => -50,
        ]);

        $this->assertEquals(400, $this->client->getResponse()->getStatusCode(), $this->client->getResponse()->getContent());
    }
}
