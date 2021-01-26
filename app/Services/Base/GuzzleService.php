<?php

namespace App\Services\Base;

use GuzzleHttp\Client;
use Hyperf\Guzzle\ClientFactory;

class GuzzleService
{
    /**
     * @var ClientFactory
     */
    private $clientFactory;

    public function __construct(ClientFactory $clientFactory)
    {
        $this->clientFactory = $clientFactory;
    }

    public function create(array $options = []): Client
    {
        return $this->clientFactory->create($options);
    }
}
