<?php

namespace Detrack\DetrackCore\Factory\Exception;

class NoClientAttachedException extends \Exception{
  public function __construct($message, $client = NULL, Exception $previous = NULL){
    $message = $message . "\n Attempted client object: " . $client;
    parent::__construct($message,0,$previous);
  }
  public function __toString(){
    return __CLASS__ . ": {$this->message}\n";
  }
}

 ?>
