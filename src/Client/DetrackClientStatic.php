<?php

namespace Detrack\DetrackCore\Client;

use GuzzleHttp\Client as HttpClient;

class DetrackClientStatic
{
    private static $httpClient;
    private static $apiKey;
    private static $jwt;
    /** @var string The Base URI for the API.
     * Private constants were only added in PHP 7.1. We will use a private static var instead.
     **/
    private static $baseURI = 'https://app.detrack.com/api/v2/';

    /**
     * Sets the "default" API Key for the Detrack Client for this application.
     *
     * @param string $newApiKey The API Key you wish to set as the default API Key
     *
     * @return void
     */
    public static function setApiKey(string $newApiKey): void
    {
        static::$apiKey = $newApiKey;
    }

    /**
     * Sends a HTTP request to the API with the given HTTP verb and path, and returns the JSON-decoded response.
     *
     * @netcall 1
     *
     * @param string $verb       the HTTP verbs POST, GET, PUT, DELETE etc
     * @param string $actionPath the path you want to send the request to
     * @param mixed  $dataArray  the array of data or the stdClass you want to send
     *
     * @throws \Exception If API Key is not set
     *
     * @return object|\Guzzle\Http\Message\Response parsed JSON response as a stdClass if the response body contains valid JSON, or the original Response object if the response body does not contain valid JSON
     */
    public static function sendData(string $verb, string $actionPath, $dataArray)
    {
        $verb = strtoupper($verb);
        if (!isset(static::$apiKey)) {
            throw new Exception('API Key Not Set');
        }
        if (!isset(static::$httpClient)) {
            static::$httpClient = new HttpClient([
                'base_uri' => static::$baseURI,
                'http_errors' => false,
            ]);
        }
        if ($verb == 'POST' || $verb != 'GET') {
            $response = static::$httpClient->request($verb, $actionPath, [
                'json' => [
                    'data' => $dataArray,
                ],
                'headers' => ['X-API-KEY' => static::$apiKey],
            ]);
        } elseif ($verb == 'GET') {
            $response = static::$httpClient->request($verb, $actionPath, [
                'query' => $dataArray,
                'headers' => ['X-API-KEY' => static::$apiKey],
            ]);
        }
        if (isset($response->getHeader('Content-Type')[0]) && $response->getHeader('Content-Type')[0] == 'application/json') {
            $responseJSON = json_decode((string) $response->getBody());
            if (!is_null($responseJSON)) {
                return $responseJSON;
            } else {
                return $response;
            }
        } else {
            return $response;
        }
    }

    /**
     * Returns the JSON Web Token (JWT), if the client has done so in this session.
     *
     * If it is null, you should either manually set it via DetrackClientStatic::setJWT(DetrackClientStatic::retrieveJWT()).
     *
     * @see static::setJWT()
     * @see static::retrieveJWT()
     *
     * @return string the associated JWT
     */
    public static function getJWT(): string
    {
        return static::$jwt;
    }

    /**
     * Sets the JSON Web Token (JWT).
     *
     * @param string $newJWT the new JWT you want to set as the default for this session
     *
     * @return void
     */
    public static function setJWT($newJWT): void
    {
        static::$jwt = $newJWT;
    }

    /**
     * Get a new JWT without setting it as the static default.
     *
     * Calls the login path to retrieve a new JWT, but does not actually set the static $jwt class property.
     * This is for you to use with DetrackClientStatic::setJWT() or to save it in some persistent (or transient) storage in your application to set it across requests.
     *
     * @netcall 1
     *
     * @see static::setJWT()
     *
     * @throws \Exception if the api key is not yet set
     *
     * @return string $token the JWT token
     */
    public static function retrieveJWT(): string
    {
        if (!isset(static::$apiKey)) {
            throw new Exception('API Key is not set for retrieving JWT');
        }
        if (!isset(static::$httpClient)) {
            static::$httpClient = new HttpClient([
                'base_uri' => static::$baseURI,
                'http_errors' => false,
            ]);
        }
        if (!isset(static::$jwt)) {
            $response = static::$httpClient->request('POST', 'login', [
                'json' => [
                    'data' => [
                        'api_key' => static::$apiKey,
                    ],
                ],
            ]);
            $responseJSON = json_decode((string) $response->getBody());
            if (!is_null($responseJSON) && is_string($responseJSON->token)) {
                return $responseJSON->token;
            }
        }
    }
}
