<?php

namespace App\Controller;

use App\Service\ProductService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Serializer\SerializerInterface;

class ProductController extends AbstractController
{
    private $productService;

    public function __construct(ProductService $productService)
    {
        $this->productService = $productService;
    }

    #[Route('/api/products', name: 'get_products', methods: ['GET'])]
    public function getProducts(): JsonResponse
    {
        $products = $this->productService->getProducts();

        return $this->json($products, JsonResponse::HTTP_OK);
    }

    #[Route('/api/products/{id}', methods: ['GET'])]
    public function getProductById(int $id): JsonResponse
    {
        $product = $this->productService->getProductById($id);
        if (!$product) {
            return $this->json(['error' => 'Product not found!'], JsonResponse::HTTP_NOT_FOUND);
        }

        return $this->json($product, JsonResponse::HTTP_OK);
    }

    #[Route('/api/products', name: 'create_product', methods: ['POST'])]
    public function create(Request $request, EntityManagerInterface $em, SerializerInterface $serializer): JsonResponse
    {
        $data = json_decode($request->getContent(), true);

        $product = $this->productService->createProduct($data);

        return $this->json($product, JsonResponse::HTTP_CREATED);
    }
}
