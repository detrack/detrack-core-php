<?php

namespace Detrack\DetrackCore\Model;

use Detrack\DetrackCore\Client\DetrackClient;

abstract class Model{
  protected $client;
  public function setClient(DetrackClient $client){
    $this->client = $client;
    return $this;
  }
  public function __construct($attr=[],$client=NULL){
    //convert array/stdClass to array, and get rid of ''
    $attr = array_filter(json_decode(json_encode($attr),true));
    foreach($this->attributes as $key=>$value){
      if(isset($attr[$key])){
        $this->attributes[$key] = $attr[$key];
      }
    }
    if($client!=NULL){
      $this->client = $client;
    }
  }
  public function __get($key){
    return $this->attributes[$key];
  }
  public function __set($key,$value){
    $this->attributes[$key] = $value;
  }
}

 ?>
