<?php

declare(strict_types = 1);

namespace McMatters\GoogleTranslateApi\Http;

use GuzzleHttp\Client as GuzzleClient;
use McMatters\GoogleTranslateApi\Exceptions\GoogleTranslateException;
use Throwable;
use const true;
use function array_merge_recursive, json_decode;

/**
 * Class Client
 *
 * @package McMatters\GoogleTranslateApi\Http
 */
class Client
{
    /**
     * @var string
     */
    protected $apiKey;

    /**
     * @var GuzzleClient
     */
    protected $client;

    /**
     * Client constructor.
     *
     * @param string $apiKey
     * @param string $baseUrl
     */
    public function __construct(string $apiKey, string $baseUrl)
    {
        $this->apiKey = $apiKey;
        $this->client = new GuzzleClient([
            'base_uri' => $baseUrl,
        ]);
    }

    /**
     * @param string $uri
     * @param array $query
     *
     * @return array
     * @throws GoogleTranslateException
     */
    public function get(string $uri, array $query = []): array
    {
        return $this->request('get', $uri, ['query' => $query]);
    }

    /**
     * @param string $uri
     * @param array $data
     *
     * @return array
     * @throws GoogleTranslateException
     */
    public function post(string $uri, array $data = []): array
    {
        return $this->request('post', $uri, ['json' => $data]);
    }

    /**
     * @param string $method
     * @param string $uri
     * @param array $options
     *
     * @return array
     * @throws GoogleTranslateException
     */
    protected function request(
        string $method,
        string $uri,
        array $options = []
    ): array {
        try {
            $response = $this->client->request(
                $method,
                $uri,
                array_merge_recursive($options, ['query' => ['key' => $this->apiKey]])
            );

            return json_decode($response->getBody()->getContents(), true);
        } catch (Throwable $e) {
            throw new GoogleTranslateException($e->getMessage(), $e->getCode(), $e);
        }
    }
}
