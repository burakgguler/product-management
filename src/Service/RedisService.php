<?php

namespace App\Service;

use Predis\Client;

class RedisService implements RedisServiceInterface
{
    private $client;

    public function __construct(string $redisHost)
    {
        $this->client = new Client($redisHost);
    }

    public function set(string $key, $value): void
    {
        $this->client->set($key, $value);
    }

    public function get(string $key): ?string
    {
        return $this->client->get($key);
    }

    public function getKeys(string $pattern): array
    {
        return $this->client->keys($pattern);
    }
}