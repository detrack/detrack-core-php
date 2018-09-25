<?php

require_once 'vendor/autoload.php';

try {
    $dotenv = new Dotenv\Dotenv(__DIR__.'/..');
    $dotenv->load();
    $apiKey = getenv('DETRACK_TESTING_API_KEY');
    Detrack\DetrackCore\Client\DetrackClientStatic::setApiKey($apiKey);
} catch (Exception $ex) {
    throw new RuntimeException('.env file not found. Please refer to .env.example and create one.');
}
