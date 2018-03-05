<?php

namespace Detrack\DetrackCore\Factory;

use Detrack\DetrackCore\Factory\Factory;
use Detrack\DetrackCore\Client\DetrackClient;
use Detrack\DetrackCore\Factory\Exception\NoClientAttachedException;
use Detrack\DetrackCore\Model\Delivery;
use Detrack\DetrackCore\Factory\ItemFactory;

class DeliveryFactory extends Factory{
  public function __construct(DetrackClient $client=NULL){
    if($client==NULL){
      if(static::$defaultClient!=NULL){
        $client = static::$defaultClient;
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
  *
  * @return Array an array of fake deliveries
  */
  public function createFakes($num=1){
    $newArray = [];
    for($i=0;$i<$num;$i++){
      $newDelivery = new Delivery([
        "date"=>\Carbon\Carbon::now()->toDateString(),
        "do"=>rand(0,99999999999)."-".\Carbon\Carbon::now()->toTimeString(),
        "address"=>"Null island",
        "items"=>ItemFactory::fakes(rand(1,10))
      ]);
      $newDelivery->setClient($this->client);
      array_push($newArray, $newDelivery);
    }
    return $newArray;
  }
  /**
  * Create a new delivery object, either blank or filled with whatever the user gave
  *
  * @param Array $attr attributes you want to pass to the new delivery
  *
  * @return Delivery the delivery object
  */
  public function createNew($attr=[]){
    $newDelivery = new Delivery($attr);
    $newDelivery->setClient($this->client);
    return $newDelivery;
  }
}


 ?>
