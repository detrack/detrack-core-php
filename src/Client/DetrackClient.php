<?php
namespace Detrack\DetrackCore\Client;

use Detrack\DetrackCore\Client\Exception\InvalidAPIKeyException;
use Detrack\DetrackCore\Model\Delivery;
use Detrack\DetrackCore\Model\Vehicle;
use Detrack\DetrackCore\Client\Traits\DeliveryMiscActions;
use \GuzzleHttp\Client as httpClient;


class DetrackClient{
  private $httpClient;
  private $apiKey;
  private $baseURI = "https://app.detrack.com/api/v1/";
  use DeliveryMiscActions;
  public function __construct($apiKey,$proxy=NULL){
    //perform preliminary checks during construction
    if(is_null($apiKey)){
      throw new InvalidAPIKeyException("No API Key given to this client");
    }
    if(!is_string($apiKey)){
      throw new InvalidAPIKeyException("API Key is not a string!",$apiKey);
    }
    if(preg_match("/[^A-Za-z0-9]/",$apiKey)){
      throw new InvalidAPIKeyException("API Key contains illegal characters",$apiKey);
    }
    //preliminary checks end
    //attach guzzlehttp client to this instance
    if($proxy==NULL){
      $this->httpClient = new httpClient([
        "base_uri" => $this->baseURI
      ]);
    }else{
      $this->httpClient = new httpClient([
        "base_uri" => $this->baseURI,
        "proxy" => "tcp://localhost:".$proxy
      ]);
    }
    $this->apiKey = $apiKey;
  }
  public function sendData($actionPath,$dataArray){
    $response = $this->httpClient->request("POST",$actionPath, [
      "json" => $dataArray,
      "headers" => ["X-API-KEY" => $this->apiKey],
      //"http_errors" => false //we will create our own exception handlers
    ]);
    return $response;
  }
  public function findVehicle($vehicleName){
    $apiPath = "vehicles/view.json";
    $response = json_decode((String) $this->sendData($apiPath,$vehicleName)->getBody());
    if($response->info->status!="ok" || $response->results[0]->status!="ok"){
      if(isset($response->results[0]->status) && $response->results[0]->status=="failed"){
        return NULL;
      }else{
        var_dump($vehicleName);
        var_dump($response);
        throw new \RuntimeException("An error occurred while retrieving vehicle data");
      }
    }else{
      return new Vehicle($response->results[0]);
    }
  }
}

 ?>
