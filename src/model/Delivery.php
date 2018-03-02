<?php

namespace Detrack\DetrackCore\Model;

use Detrack\DetrackCore\Repository\DeliveryRepository;
use Detrack\DetrackCore\Model\ItemCollection;
use Intervention\Image\ImageManagerStatic as Image;
use \RuntimeException;

class Delivery extends Model{
  use DeliveryRepository;
  /**
  * Attributes a delivery model has.
  * Not all of these attributes are compulsory. Required values are to be specified in the $requiredAttributes static variable
  * Fields marked REQUIRED are required by the Detrack API for the most basic functionality.
  * Fields marked OPTIONAL are optional but still utilised by the Detrack Backend System if you supply them.
  * Fields marked EXTRA (or not marked) are arbitary custom fields that are up to the discretion of the Detrack user to decide what they are used for.
  * Required: date, do, address
  */
  protected $attributes = [
    "deliver_to" => NULL, //OPTIONAL: The name of the recipient to deliver to. This can be a person’s name e.g. John Tan, a company’s name e.g. ABC Inc., or both e.g. John Tan (ABC Inc.)
    "delivery_time" => NULL, //OPTIONAL: The delivery time window. This will be displayed in the job list view and the delivery detail view on the app.
    "status" => NULL,
    "open_job" => NULL,
    "offer" => NULL,
    "do" => NULL, //REQUIRED: The delivery order number. This attribute must be unique for this date.
    "date" => NULL, //REQUIRED: The delivery date. Format: YYYY-MM-DD.
    "start_date" => NULL,
    "sync_time" => NULL,
    "time" => NULL,
    "time_slot" => NULL,
    "req_date" => NULL,
    "track_no" => NULL,
    "order_no" => NULL,
    "job_type" => NULL,
    "job_order" => NULL,
    "job_fee" => NULL,
    "address" => NULL, //REQUIRED: The full address. Always include country name for accurate geocoding results.
    "addr_company" => NULL,
    "addr_1" => NULL,
    "addr_2" => NULL,
    "addr_3" => NULL,
    "postal_code" => NULL,
    "city" => NULL,
    "state" => NULL,
    "country" => NULL,
    "billing_add" => NULL,
    "name" => NULL,
    "phone" => NULL, // OPTIONAL: The phone number of the recipient. If specified, the driver can call the recipient directly from the app.
    "sender_phone" => NULL,
    "fax" => NULL,
    "instructions" => NULL, //OPTIONAL: Any special delivery instruction for the driver. This will be displayed in the delivery detail view on the app.
    "assign_to" => NULL, //OPTIONAL: The name of the vehicle to assign this delivery to. This must be spelled exactly the same as your vehicle’s name in your dashboard.
    "notify_email" => NULL, //OPTIONAL: The email address to send customer-facing delivery updates to. If specified, a delivery notification will be sent to this email address upon successful delivery.
    "notify_url" => NULL, //OPTIONAL: The URL to post delivery updates to. Please refer to "Delivery Push Notification" on the our documentation.
    "zone" => NULL, //OPTIONAL: If you divide your deliveries into zones, then specifying this will help you to easily filter out the deliveries by zones in your dashboard.
    "customer" => NULL,
    "acc_no" => NULL,
    "owner_name" => NULL,
    "invoice_no" => NULL,
    "invoice_amt" => NULL,
    "pay_mode" => NULL,
    "pay_amt" => NULL,
    "group_name" => NULL,
    "src" => NULL,
    "wt" => NULL,
    "cbm" => NULL,
    "boxes" => NULL,
    "cartons" => NULL,
    "pcs" => NULL,
    "envelopes" => NULL,
    "pallets" => NULL,
    "bins" => NULL,
    "trays" => NULL,
    "bundles" => NULL,
    "att_1" => NULL,
    "depot" => NULL,
    "depot_contact" => NULL,
    "sales_person" => NULL,
    "identification_no" => NULL,
    "bank_prefix" => NULL,
    "reschedule" => NULL,
    "pod_at" => NULL,
    "reason" => NULL,
    "ITEM-LEVEL" => NULL,
    "sku" => NULL,
    "po_no" => NULL,
    "batch_no" => NULL,
    "expiry" => NULL,
    "desc" => NULL,
    "cmts" => NULL,
    "qty" => NULL,
    "uom" => NULL,
    "items" => [] //OPTIONAL: array of items to add to the delivery. Will be changed in constructor.
  ];
  /**
  * Required attributes are defined here
  */
  protected static $requiredAttributes = ["date","do","address"];
  /**
  * Define error code constants returned by the API when calling delivery endpoints
  *
  * Why are these defined here and not in DeliveryRepository, you ask?
  * IDK, ask PHP why I can't define constants in traits.
  */
  const ERROR_CODE_INVALID_ARGUMENT = "1000";
  const ERROR_CODE_INVALID_KEY = "1001";
  const ERROR_CODE_DELIVERY_ALREADY_EXISTS = "1002";
  const ERROR_CODE_DELIVERY_NOT_FOUND = "1003";
  const ERROR_CODE_DELIVERY_NOT_EDITABLE = "1004";
  const ERROR_CODE_DELIVERY_NOT_DELETABLE = "1005";
  /**
  * Constructor function for Delivery model
  */
  public function __construct($attr=[],$client=NULL){
    parent::__construct();
    //initialise items
    if(isset($attr["items"]){
      if(is_array($attr["items"])){
        $this->items = new ItemCollection($attr["items"]);
      }else if($attr["items"] instanceof ItemCollection){
        $this->items = $attr["items"];
      }
    }else{
      $this->items = new ItemCollection();
    }
  }
  /**
  * Get the unqiue idenitifier of the delivery object used to find the delivery object in the database
  *
  * Use this together with the find() function
  *
  * @return Array an associative array with indexes "date" and "do"
  */
  public function getIdentifier(){
    return ["date"=>$this->date,"do"=>$this->do];
  }
  /**
  * Downloads the image of POD with specified index as an Intervention\Image instanceof
  *
  * @param Integer $no Which image file (1-5) to download
  *
  * @throws RuntimeException if param is not an integer from 1 to 5
  *
  * @return Intervention\Image|NULL the POD image file, NULL if not found
  */
  public function getPODImage($no){
    $no = (int) $no;
    if(!is_int($no) || $no < 1 || $no > 5){
      throw new \RuntimeException("POD Image Number must be between 1 to 5");
    }
    try{
      $response = $this->client->sendData("deliveries/photo_".$no.".json",$this->getIdentifier())->getBody();
      $img = Image::make($response);
      return $img;
    }catch(\GuzzleHttp\Exception\ClientException $ex){
      //we got 404'd
      return NULL;
    }
  }
  /**
  * Downloads and saves an image of the proof of delivery to the path (with filename) specified.
  *
  * This automatically saves the file to disk. If you want to do some image editing before saving, please use getPODImage instead.
  *
  * @param Integer $no Which image file (1-5) to download
  * @param String $path the path you want to download the file to (including the name)
  *
  * @throws Exception if the path is not writable
  * @throws RuntimeException if there is no POD image with the specified index on the server, or the delivery is not found
  *
  * @return Boolean returns true if the download is successful
  */
  public function downloadPODImage($no,$path){
    $img = $this->getPODImage($no);
    if($img==NULL){
      throw new \RuntimeException("POD Image does not exist");
    }
    $dir = substr($path,0,strrpos($path,DIRECTORY_SEPARATOR));
    //if path is /tmp/img/pod.jpg, should return /tmp/img
    if(!file_exists($dir)){
      mkdir($dir,0750,true);
    }else{
      $img->save($path);
      return true;
    }
  }
  /**
  * Downloads the POD in pdf format. Returns a binary string.
  *
  * This does not automatically save the file to disk. If that's what you're looking for, use downloadPODPDF instead.
  *
  * @return String|NULL the binary data of the pdf, NULL if no pdf is present
  */
  public function getPODPDF(){
    try{
      $response = (String) $this->client->sendData("deliveries/export.pdf",$this->getIdentifier())->getBody();
      return $response;
    }catch(\GuzzleHttp\Exception\ClientException $ex){
      //we got 404'd
      return NULL;
    }
  }
  /**
  * Downloads the POD in pdf format and writes it to the given path.
  *
  * This automatically saves to disk. If you only want the file data without saving, use getPODPDF instead.
  *
  * @param String $path The path you want to write to (with filename)
  *
  * @throws Exception if the path is not writable
  * @throws RuntimeException if there is no POD PDF on the server
  * @return boolean if download succeeds
  */
  public function downloadPODPDF($path){
    $response = $this->getPODPDF();
    if(is_null($response)){
      throw new \RuntimeException("There is no POD PDF available to retrieve for this delivery, or wrong delivery details were given");
    }
    $file = fopen($path,"w");
    return (bool) fwrite($file, $response);
  }
}


 ?>
