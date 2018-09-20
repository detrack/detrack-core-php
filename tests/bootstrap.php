<?php

require_once 'vendor/autoload.php';

try {
    $dotenv = new Dotenv\Dotenv(__DIR__.'/..');
    $dotenv->load();
} catch (Exception $ex) {
    throw new RuntimeException('.env file not found. Please refer to .env.example and create one.');
}
