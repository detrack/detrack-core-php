<?php

use Detrack\DetrackCore\Client\DetrackClientStatic;
use PHPUnit\Framework\TestCase;

final class DetrackClientStaticTest extends TestCase
{
    public function testRetrieveAPIKey()
    {
        $apiKey = getenv('DETRACK_TESTING_API_KEY');
        DetrackClientStatic::setApiKey($apiKey);
        DetrackClientStatic::setJWT(DetrackClientStatic::retrieveJWT());
        $response = DetrackClientStatic::sendData('GET', 'api_key', []);
        $this->assertEquals($apiKey, $response->data->api_key);
    }
}
