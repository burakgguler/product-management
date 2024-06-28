<?php

namespace App\Service;

use App\Entity\Product;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Serializer\SerializerInterface;

class ProductService
{
    private $em;
    private $serializer;
    private $redisService;

    public function __construct(EntityManagerInterface $em, SerializerInterface $serializer, RedisService $redisService)
    {
        $this->em = $em;
        $this->serializer = $serializer;
        $this->redisService = $redisService;
    }

    public function createProduct(array $data): Product
    {
        $product = new Product();
        $product->setName($data['name']);
        $product->setCategory($data['category']);
        $product->setPrice($data['price']);
        $product->setStock($data['stock']);
        $product->setSku($this->generateSku());

        $this->em->persist($product);
        $this->em->flush();

        $this->storeProductInRedis($product);

        return $product;
    }

    public function getProducts(): array
    {
        $products = [];
        $keys = $this->redisService->getKeys('product_*');

        if (empty($keys)) {
            $products = $this->fetchAllProductsFromDatabase();
            foreach ($products as $product) {
                $this->storeProductInRedis($product);
            }
        } else {
            foreach ($keys as $key) {
                $products[] = json_decode($this->redisService->get($key), true);
            }
        }

        return $products;
    }

    public function getProductById(int $id)
    {
        $product = $this->redisService->get('product_' . $id);
        if (!$product) {
            $product = $this->fetchProductFromDatabaseById($id);
            if ($product) {
                $this->storeProductInRedis($product);
                return $product;
            }
            return null;
        }

        return $this->serializer->deserialize($product, Product::class, 'json');
    }

    public function fetchAllProductsFromDatabase(): array
    {
        return $this->em->getRepository(Product::class)->findAll();
    }

    public function fetchProductFromDatabaseById(int $id): ?Product
    {
        return $this->em->getRepository(Product::class)->find($id);
    }

    public function storeProductInRedis(Product $product): void
    {
        $this->redisService->set('product_' . $product->getId(), $this->serializer->serialize($product, 'json'));
    }

    public function generateSku(): string
    {
        return strtoupper(substr(md5(uniqid()), 0, 8));
    }
}