<?php

namespace Detrack\DetrackCode\Repository\Exception;

class MissingFieldException extends \Exception{
  public function __construct($object,$missingField, Exception $previous = NULL){
    $message = "Field required to save object ". $object  . " : " . $missingField;
    parent::__construct($message,0,$previous);
  }
  public function __toString(){
    return __CLASS__ . ": {$this->message}\n";
  }
}


 ?>
