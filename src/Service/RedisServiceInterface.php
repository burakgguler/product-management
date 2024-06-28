<?php

namespace App\Service;

interface RedisServiceInterface
{
    public function getKeys(string $pattern): array;
    public function get(string $key);
    public function set(string $key, $value): void;
}
