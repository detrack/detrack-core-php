<?php

namespace Detrack\DetrackCore\Model;

use Detrack\DetrackCore\Client\DetrackClient;

abstract class Model implements \JsonSerializable{
  protected $client;
  /**
  * An associative array that stores what values have been updated since the last save() function calls
  *
  * We will store the names of the stored attributes in keys, not in values because it is faster
  */
  protected $modifiedAttributes = [];
  protected static $requiredAttributes = [];
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
    $this->modifiedAttributes[$key] = true;
    $this->attributes[$key] = $value;
  }
  /**
  * Return attributes that PHP's json_encode will act on
  *
  * Because the API will treat values entered as NULL as deleting, we will remove null values except where it was modified
  *
  * @return Array the model's array attributes
  */
  public function jsonSerialize(){
    return array_filter($this->attributes,function($attribute){
      return isset($this->modifiedAttributes[$attribute]) || in_array($attribute,static::$requiredAttributes);
    },ARRAY_FILTER_USE_KEY);
  }
  /**
  * Reset the modifiedAttributes array
  */
  protected function resetModifiedAttributes(){
    $this->modifiedAttributes = [];
  }
  /**
  * Defines __toString() magic method for debugging purposes
  *
  * For now, calls json_encode on itself (and thus jsonSerialize()).
  *
  * @return String String representation of the model
  */
  public function __toString(){
    return json_encode($this);
  }
}

 ?>
