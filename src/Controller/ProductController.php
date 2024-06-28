<?php

namespace App\Controller;

use App\Service\ProductSearchService;
use App\Service\ProductService;
use App\Validator\ProductValidator;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class ProductController extends AbstractController
{
    private $productService;
    private $productSearchService;
    private $productValidator;

    public function __construct
    (
        ProductService $productService,
        ProductSearchService $productSearchService,
        ProductValidator $productValidator
    )
    {
        $this->productService = $productService;
        $this->productSearchService = $productSearchService;
        $this->productValidator = $productValidator;
    }

    #[Route('/api/products/search', name: 'search_products', methods: ['GET'])]
    public function searchProducts(Request $request): JsonResponse
    {
        $query = $request->query->get('q', '');

        if (empty($query)) {
            return $this->json(['error' => 'Query parameter is required!'], Response::HTTP_BAD_REQUEST);
        }

        $products = $this->productSearchService->searchProducts($query);

        if (empty($products)) {
            return $this->json(['message' => 'No products found.'], Response::HTTP_NOT_FOUND);
        }

        return $this->json($products);
    }

    #[Route('/api/products', name: 'get_products', methods: ['GET'])]
    public function getProducts(): JsonResponse
    {
        $products = $this->productService->getProducts();

        if (empty($products)) {
            return $this->json(['message' => 'No products found.'], Response::HTTP_NOT_FOUND);
        }

        return $this->json($products);
    }

    #[Route('/api/products/{id}', methods: ['GET'])]
    public function getProductById(int $id): JsonResponse
    {
        $product = $this->productService->getProductById($id);

        if (!$product) {
            return $this->json(['error' => 'Product not found!'], Response::HTTP_NOT_FOUND);
        }

        return $this->json($product);
    }

    #[Route('/api/products', name: 'create_product', methods: ['POST'])]
    public function create(Request $request): JsonResponse
    {
        $data = json_decode($request->getContent(), true);

        $errors = $this->productValidator->validate($data);
        if (count($errors) > 0) {
            $errorMessages = [];
            foreach ($errors as $error) {
                $field = $error->getPropertyPath();
                $errorMessages[$field][] = $error->getMessage();
            }

            return $this->json(['errors' => $errorMessages], Response::HTTP_BAD_REQUEST);
        }

        $product = $this->productService->createProduct($data);

        return $this->json($product, Response::HTTP_CREATED);
    }
}
