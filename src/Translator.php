<?php

declare(strict_types = 1);

namespace McMatters\GoogleTranslateApi;

use InvalidArgumentException;
use McMatters\GoogleTranslateApi\Http\Client;
use const null;
use function md5, preg_replace, substr;

/**
 * Class Translator
 *
 * @package McMatters\GoogleTranslateApi
 */
class Translator
{
    const BASE_URL = 'https://translation.googleapis.com/language/translate/';
    const API_VERSION = 'v2';

    const FORMAT_HTML = 'html';
    const FORMAT_TEXT = 'text';

    /**
     * @var Client
     */
    protected $httpClient;

    /**
     * Translator constructor.
     *
     * @param string $apiKey
     */
    public function __construct(string $apiKey)
    {
        $this->httpClient = new Client($apiKey, self::BASE_URL.self::API_VERSION.'/');
    }

    /**
     * @param string $q
     * @param string $target
     * @param string $format
     * @param string|null $source
     * @param string|null $model
     *
     * @return array
     * @throws \McMatters\GoogleTranslateApi\Exceptions\GoogleTranslateException
     * @throws \InvalidArgumentException
     */
    public function translate(
        string $q,
        string $target,
        string $format = self::FORMAT_HTML,
        string $source = null,
        string $model = null
    ): array {
        $this->checkFormat($format);

        $data = $this->httpClient->post('', [
            'q' => $q,
            'target' => $target,
            'format' => $format,
            'source' => $source,
            'model' => $model,
        ]);

        return $data['data']['translations'] ?? [];
    }

    /**
     * @param string $q
     * @param string $target
     * @param string $preservePattern
     * @param string|null $source
     * @param string|null $model
     *
     * @return array
     * @throws \McMatters\GoogleTranslateApi\Exceptions\GoogleTranslateException
     * @throws \InvalidArgumentException
     */
    public function translateWithPreservation(
        string $q,
        string $target,
        string $preservePattern,
        string $source = null,
        string $model = null
    ): array {
        // This is necessary in order to save user span tags.
        $id = substr(md5(__NAMESPACE__), 0, 12);

        $q = preg_replace(
            "/({$preservePattern})/i",
            "<span id='{$id}' translate='no'>$1</span>",
            $q
        );

        $translations = $this->translate($q, $target, self::FORMAT_HTML, $source, $model);

        foreach ($translations as &$translation) {
            $translation['translatedText'] = preg_replace(
                "/<span id='{$id}' translate='no'>({$preservePattern})<\/span>/i",
                '$1',
                $translation['translatedText']
            );
        }

        return $translations;
    }

    /**
     * @param string $q
     *
     * @return array
     * @throws Exceptions\GoogleTranslateException
     */
    public function detect(string $q): array
    {
        $data = $this->httpClient->post('detect', ['q' => $q]);

        return $data['data']['detections'] ?? [];
    }

    /**
     * @param string|null $target
     * @param string|null $model
     *
     * @return array
     * @throws Exceptions\GoogleTranslateException
     */
    public function languages(string $target = null, string $model = null): array
    {
        $data = $this->httpClient->get('languages', [
            'target' => $target,
            'model' => $model,
        ]);

        return $data['data']['languages'] ?? [];
    }

    /**
     * @param string $format
     *
     * @throws InvalidArgumentException
     */
    protected function checkFormat(string $format)
    {
        if ($format !== self::FORMAT_HTML && $format !== self::FORMAT_TEXT) {
            throw new InvalidArgumentException('$format must be as "html" or "text"');
        }
    }
}
