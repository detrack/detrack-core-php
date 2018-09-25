<?php

namespace Detrack\DetrackCore\Client;

use GuzzleHttp\Client as HttpClient;

class DetrackClientStatic
{
    private static $httpClient;
    private static $apiKey;
    private static $jwt;
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
              'base_uri' => static::baseURI,
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
        if (!isset(static::$jwt)) {
            static::$jwt = static::retrieveJWT();
        }
        $response = static::$httpClient->request($verb, $actionPath, [
          'json' => [
            'data' => $dataArray,
          ],
          'headers' => ['Authorization' => 'Bearer '.static::$jwt],
        ]);
        $responseJSON = json_decode((string) $response->getBody());
        if (!is_null($responseJSON)) {
            return $responseJSON;
        } else {
            return $response;
        }
    }
}
