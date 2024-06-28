<?php

namespace App\Controller;

use App\Service\ProductSearchService;
use App\Service\ProductService;
use App\Validator\ProductValidator;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Serializer\SerializerInterface;

class ProductController extends AbstractController
{
    private $productService;
    private $productSearchService;
    private $productValidator;

    public function __construct(ProductService $productService, ProductSearchService $productSearchService, ProductValidator $productValidator)
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
            return new JsonResponse(['error' => 'Query parameter is required!'], JsonResponse::HTTP_BAD_REQUEST);
        }

        $products = $this->productSearchService->searchProducts($query);

        if (empty($products)) {
            return new JsonResponse(['message' => 'No products found.'], JsonResponse::HTTP_NOT_FOUND);
        }

        return new JsonResponse($products);
    }

    #[Route('/api/products', name: 'get_products', methods: ['GET'])]
    public function getProducts(): JsonResponse
    {
        $products = $this->productService->getProducts();

        if (empty($products)) {
            return new JsonResponse(['message' => 'No products found.'], JsonResponse::HTTP_NOT_FOUND);
        }

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

        $errors = $this->productValidator->validate($data);
        if (count($errors) > 0) {
            $errorMessages = [];
            foreach ($errors as $error) {
                $field = $error->getPropertyPath();
                $errorMessages[$field][] = $error->getMessage();
            }

            return new JsonResponse(['errors' => $errorMessages], JsonResponse::HTTP_BAD_REQUEST);
        }

        $product = $this->productService->createProduct($data);

        return $this->json($product, JsonResponse::HTTP_CREATED);
    }
}
