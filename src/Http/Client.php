<?php

declare(strict_types=1);

namespace McMatters\GoogleTranslateApi\Http;

use McMatters\GoogleTranslateApi\Exceptions\GoogleTranslateException;
use McMatters\Ticl\Client as HttpClient;
use Throwable;

use function array_merge_recursive;

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
     * @var \McMatters\Ticl\Client
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
        $this->client = new HttpClient(['base_uri' => $baseUrl]);
    }

    /**
     * @param string $uri
     * @param array $query
     *
     * @return array
     *
     * @throws \McMatters\GoogleTranslateApi\Exceptions\GoogleTranslateException
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
     *
     * @throws \McMatters\GoogleTranslateApi\Exceptions\GoogleTranslateException
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
     *
     * @throws \McMatters\GoogleTranslateApi\Exceptions\GoogleTranslateException
     */
    protected function request(
        string $method,
        string $uri,
        array $options = []
    ): array {
        try {
            return $this->client->{$method}(
                $uri,
                array_merge_recursive($options, ['query' => ['key' => $this->apiKey]])
            )->json();
        } catch (Throwable $e) {
            throw new GoogleTranslateException($e->getMessage(), $e->getCode(), $e);
        }
    }
}
