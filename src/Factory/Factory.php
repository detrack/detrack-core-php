<?php

namespace Detrack\DetrackCore\Factory;

abstract class Factory
{
    protected static $defaultClient;
    protected $client;

    public static function setDefaultClient(DetrackClient $client)
    {
        static::$defaultClient = $client;
    }
}
