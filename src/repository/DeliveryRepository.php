<?php

namespace Detrack\DetrackCore\Repository;

use Detrack\DetrackCore\Client\DetrackClient;
use Detrack\DetrackCore\Repository\Exception\MissingFieldException;
use Detrack\DetrackCore\Factory\Exception\NoClientAttachedException;
use Detrack\DetrackCore\Model\Delivery;

trait DeliveryRepository{
  use Repository;
  /**
  * Saves the Delivery model by sending a HTTP request to the API, with the key registered to the client attached to this model.
  *
  * Actually, it tries to see if a delivery already exists, then it chooses whether to send to the Edit API or the Create API.
  * @return void
  */
  public function save(DetrackClient $client=NULL){
    if($client==NULL && $this->client==NULL){
      throw new NoClientAttachedException("No client attached");
    }
    if($client!=NULL && $this->client==NULL){
      $this->client = $client;
    }
    if($this->client->findDelivery($this->getIdentifier())!=NULL){
      $this->update();
    }else{
      static::create($this);
    }
    $this->resetModifiedAttributes();
  }
  /**
  * Sends HTTP request to the create new delivery endpoint
  *
  * @throws MissingFieldException if the required fields are not present
  * @return void
  */
  private static function create($delivery){
      $apiPath = "deliveries/create.json";
      $dataArray = $delivery->attributes;
      //check for required fields;
      if($dataArray["date"]==NULL){
        //maybe set default date?
      }else if($dataArray["do"]==NULL){
        throw new MissingFieldException("Delivery","do");
      }else if($dataArray["address"]==NULL){
        throw new MissingFieldException("Delivery","address");
      }
      $response = $delivery->client->sendData($apiPath,$dataArray);
      $responseObj = json_decode((String) $response->getBody());
      return $responseObj;
  }
  /**
  * Sends HTTP request to the edit delivery endpoint
  *
  * This is for editing a single delivery only.
  *
  * @throws MissingFieldException if the required fields are not present
  */
  private function update(){
    $apiPath = "deliveries/update.json";
    $dataArray = $this->attributes;
    if($dataArray["date"]==NULL){
      //maybe set default date?
    }else if($dataArray["do"]==NULL){
      throw new MissingFieldException("Delivery","do");
    }else if($dataArray["address"]==NULL){
      throw new MissingFieldException("Delivery","address");
    }
    $response = $this->client->sendData($apiPath,$dataArray);
    $responseObj = json_decode((String) $response->getBody());
    return $responseObj;
  }
  /**
  * Sends HTTP request to the delete deliveries endpoint
  *
  * This is for deleting a single delivery object only.
  *
  * @throws MissingFieldException if date or do fields are somehow missing from the object
  * @return Boolean true if delete was successful, false if apache_note
  */
  public function delete(){
    $apiPath = "deliveries/delete.json";
    $dataArray = $this->getIdentifier();
    if($dataArray["date"]==NULL){
      throw new MissingFieldException("Delivery","date");
    }else if($dataArray["do"]==NULL){
      throw new MissingFieldException("Delivery","do");
    }
    $response = $this->client->sendData($apiPath,$dataArray);
    $responseObj = json_decode((String) $response->getBody());
    if($responseObj->info->failed!=0){
      return false;
    }else{
      return true;
    }
  }
}


 ?>
