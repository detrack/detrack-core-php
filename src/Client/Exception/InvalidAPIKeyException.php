<?php

namespace Detrack\DetrackCore\Client\Exception;

class InvalidAPIKeyException extends \Exception{
  public function __construct($message, $apiKey = NULL, Exception $previous = NULL){
    $message = $message . "\n Attempted API Key: " . $apiKey;
    parent::__construct($message,0,$previous);
  }
  public function __toString(){
    return __CLASS__ . ": {$this->message}\n";
  }
}

 ?>
