<?php

namespace Detrack\DetrackCore\Factory;

use Detrack\DetrackCore\Factory\Factory;
use Detrack\DetrackCore\Client\DetrackClient;
use Detrack\DetrackCore\Factory\Exception\NoClientAttachedException;
use Detrack\DetrackCore\Model\Delivery;

class DeliveryFactory extends Factory{
  public function __construct(DetrackClient $client=NULL){
    if($client==NULL){
      if(static::client!=NULL){
        $client = static::client;
      }else{
        throw new NoClientAttachedException("No client passed in factory constructor, or no default client set");
      }
    }else if(! ($client instanceof DetrackClient)){
      throw new NoClientAttachedException("Object passed in constructor is not an instance of DetrackClient");
    }
    $this->client = $client;
  }
  /**
  * Creates one more many delivery objects with the client and fake data automatically set
  *
  * @param Integer specify how many to create
  */
  public function createFakes($num=1){
    $newArray = [];
    for($i=0;$i<$num;$i++){
      $newDelivery = new Delivery([
        "date"=>\Carbon\Carbon::now()->toDateString(),
        "do"=>rand(0,99999999999),
        "address"=>"Null island"
      ]);
      array_push($newArray, $newDelivery);
    }
    return $newArray;
  }
}


 ?>
