<?php

namespace Detrack\DetrackCore\Client;

use GuzzleHttp\Client as HttpClient;

class DetrackClientStatic
{
    private static $httpClient;
    private static $apiKey;
    private const baseURI = 'https://app.detrack.com/api/v2/';

    /**
     * Sets the "default" API Key for the Detrack Client for this application.
     *
     * @param string $newApiKey The API Key you wish to set as the default API Key
     */
    public static function setApiKey($newApiKey)
    {
        static::$apiKey = $newApiKey;
    }

    /**
     * Sends a HTTP request to the API with the given HTTP verb and path, and returns the JSON-decoded response.
     *
     * @param string $verb       the HTTP verbs POST, GET, PUT, DELETE etc
     * @param string $actionPath the path you want to send the request to
     * @param array  $dataArray  the array of data you want to send
     *
     * @return stdClass $response the JSON-decoded response data, without any filtering
     */
    public static function sendData($verb, $actionPath, $dataArray)
    {
        if (!isset(static::$apiKey)) {
            return;
        }
        if (!isset(static::$httpClient)) {
            static::$httpClient = new HttpClient([
              'base_uri' => static::baseURI,
              'http_errors' => false,
            ]);
        }
        $response = static::$httpClient->request($verb, $actionPath, [
          'json' => [
            'data' => $dataArray,
          ],
          'headers' => ['X-API-KEY' => static::$apiKey],
        ]);
        $responseJSON = json_decode((string) $response->getBody());
        if (!is_null($responseJSON)) {
            return $responseJSON;
        } else {
            return $response;
        }
    }
}
