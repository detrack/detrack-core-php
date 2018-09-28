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
     * @return \Guzzle\Http\Message\Response $response returns a \stdClass if the response is valid json, the original Response object if it is not
     */
    public static function sendData($verb, $actionPath, $dataArray)
    {
        $verb = strtoupper($verb);
        if (!isset(static::$apiKey)) {
            return;
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
        if ($response->getHeader('Content-Type')[0] == 'application/json') {
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
     * Returns the JSON Web Token (JWT), if the client has done so in this session.
     * If it is null, you should either manually set it via DetrackClientStatic::setJWT(DetrackClientStatic::retrieveJWT()).
     *
     * @see static::setJWT()
     * @see static::retrieveJWT()
     *
     * @return string the associated JWT
     */
    public static function getJWT()
    {
        return static::$jwt;
    }

    /**
     * Sets the JSON Web Token (JWT).
     *
     * @param string $newJWT the new JWT you want to set as the default for this session
     */
    public static function setJWT($newJWT)
    {
        static::$jwt = $newJWT;
    }

    /**
     * Get a new JWT without setting it as the static default.
     *
     * Calls the login path to retrieve a new JWT, but does not actually set the static $jwt class property.
     * This is for you to use with DetrackClientStatic::setJWT() or to save it in some persistent (or transient) storage in your application to set it across requests.
     *
     * @see static::setJWT()
     *
     * @return string $token the JWT token
     */
    public static function retrieveJWT()
    {
        if (!isset(static::$apiKey)) {
            return;
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
