<?php

namespace Detrack\DetrackCore\Client\Traits;
/**
* Dear PHP,
* I love you, but,
* WHY DON'T YOU LET ME USE THE WORD "TRAIT" IN THE NAMESPACE
* WHY PHP WHY
*/
use Detrack\DetrackCore\Model\Delivery;

trait DeliveryMiscActions{
  /**
  * Sends HTTP request to the View delivery endpoint to find a single delivery.
  * @param Array $attr An associative array containing the keys "date" and "do" that identifies the delivery
  * @return Delivery|NULL The first delivery that matches the two fields
  */
  public function findDelivery($attr){
      $apiPath = "deliveries/view.json";
      $dataArray = $attr;
      $response = $this->sendData($apiPath,$dataArray);
      $responseObj = json_decode((String) $response->getBody());
      if($responseObj->info->status!="ok"){
        //handle errors
      }else{
        if($responseObj->results[0]->status=="failed"){
          /* assume the API only returns one error per result */;
          if($responseObj->results[0]->errors[0]->code == Delivery::ERROR_CODE_DELIVERY_NOT_FOUND){
            return NULL;
          }
        }
        $foundDelivery = new Delivery($responseObj->results[0]->delivery);
        //important to reattach the client upon creating the object, or method chaining will fail
        $foundDelivery->setClient($this);
        return $foundDelivery;
      }
  }
  /**
  * Bulk save deliveries. This is similar to Delivery::save, but does so in only two HTTP requests.
  *
  * Use this instead of multiple Delivery::save() calls to cut down the number of HTTP requests you have to make.
  * Supply an array of Delivery objects.
  * It first attempts "create" on every delivery object, collects the ones that failed because it already exists, then calls "update" on the rest.
  * Please use sparingly.
  *
  * @param Array $deliveries an array of deliveries to save
  *
  * @return Boolean|Array array of responses containing deliveries that failed either operation for either reason
  */
  public function bulkSaveDeliveries($deliveries){
    $apiPath = "deliveries/create.json";
    $dataArray = $deliveries;
    $response = $this->sendData($apiPath,$dataArray);
    $failedCreates = [];
    $failedEitherWay = [];
    foreach($response->results as $responseResult){
      if($responseResult->status == "failed"){
        foreach($responseResult->errors as $error){
          if($error->code == Delivery::ERROR_CODE_DELIVERY_ALREADY_EXISTS){
            //create has failed because it already exists
            break;
          }else{
            //create failed because of some other reason. ignore.
            array_push($failedEitherWay,$responseResult);
            continue 2;
          }
        }
        unset($responseResult->status);
        unset($responseResult->errors);
        array_push($failedCreates,$responseResult);
      }
    }
    //now call update
    $apiPath = "deliveries/update.json";
    $dataArray = $failedCreates;
    $response = $this->sendData($apiPath,$dataArray);
    if($response->info->failed==0){
      if(count($failedEitherWay)==0){
        return true;
      }else{
        return $failedEitherWay;
      }
    }else{
      foreach($response->results as $responseResult){
        if($responseResult->status=="failed"){
          array_push($failedEitherWay,$responseResult);
        }
      }
      return $failedEitherWay;
    }
  }
  /**
  * Bulk delete deliveries. This can delete deliveries on different days.
  *
  * Use this instead of multiple Delivery::delete() calls to cut down the number of HTTP requests you have to make.
  * Supply either an array of Delivery objects or an array of associative arrays returned by Delivery::getIdentifier
  * Please use sparingly.
  *
  * @param Array $paramArray this can either be an array of Delivery objects, or an array of Delivery identifier associative arrays
  *
  * @return Boolean|Array returns true if all the deletes worked, or a list of delivery identifiers that failed, with an additional index called "errors" that list the errors that occured
  */
  public function bulkDeleteDeliveries($paramArray){
    $apiPath = "deliveries/delete.json";
    $dataArray = [];
    foreach($paramArray as $paramElement){
      if($paramElement instanceof Delivery){
        array_push($dataArray,$paramElement->getIdentifier());
      }else if(is_array($paramElement)){
        if(count($paramElement)==2 && array_key_exists("date",$paramElement) && array_key_exists("do",$paramElement)){
          array_push($dataArray,$paramElement);
        }else{
          //bad element, dont do anything
        }
      }
    }
    $response = $this->sendData($apiPath,$dataArray);
    if($responseObj->info->status!="ok"){
      //handle errors
      return false;
    }else{
      for($i=0;$i<count($response->results);$i++){
        if($response->results[$i]->status!="failed"){
          unset($response->results[$i]);
        }
      }
      if(count($response->results)!=0){
        return $response->results;
      }else{
        return true;
      }
    }
  }
}

 ?>
