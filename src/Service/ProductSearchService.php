<?php

namespace App\Service;

use Elastic\Elasticsearch\ClientBuilder;
use Elastic\Elasticsearch\Exception\AuthenticationException;
use Elastic\Elasticsearch\Exception\ClientResponseException;
use Elastic\Elasticsearch\Exception\MissingParameterException;
use Elastic\Elasticsearch\Exception\ServerResponseException;

class ProductSearchService implements ProductSearchServiceInterface
{
    private $client;

    /**
     * @throws AuthenticationException
     */
    public function __construct()
    {
        $this->client = ClientBuilder::create()->setHosts([getenv('ELASTICSEARCH_URL')])->build();
    }

    /**
     * @throws ServerResponseException
     * @throws ClientResponseException
     */
    public function searchProducts(string $query): array
    {
        $params = [
            'index' => 'products',
            'body'  => [
                'query' => [
                    'multi_match' => [
                        'query'  => $query,
                        'fields' => ['name', 'category', 'sku']
                    ]
                ]
            ]
        ];

        $response = $this->client->search($params);

        $products = [];
        if (isset($response['hits']['hits'])) {
            foreach ($response['hits']['hits'] as $hit) {
                $products[] = $hit['_source'];
            }
        }

        return $products;
    }

    /**
     * @throws ServerResponseException
     * @throws ClientResponseException
     * @throws MissingParameterException
     */
    public function indexProduct(array $productData): void
    {
        $params = [
            'index' => 'products',
            'id' => $productData['id'],
            'body' => $productData
        ];

        $this->client->index($params);
    }
}