<?php

namespace App\Service;

interface ProductSearchServiceInterface
{
    public function indexProduct(array $productData): void;
    public function searchProducts(string $query): array;
}