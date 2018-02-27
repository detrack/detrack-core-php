<?php

namespace Detrack\DetrackCore\Factory;

abstract class Factory{
    private static $client;

    public static function setDefaultClient(DetrackClient $client){
      static::$client = $client;
    }
}

 ?>
