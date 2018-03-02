<?php

namespace Detrack\DetrackCore\Model;

class Item extends Model{
  /**
  * Attributes a delivery model has.
  * Not all of these attributes are compulsory.
  * Fields marked REQUIRED are required by the Detrack API for the most basic functionality.
  * Fields marked OPTIONAL are optional but still utilised by the Detrack Backend System if you supply them.
  * Fields marked EXTRA (or not marked) are arbitary custom fields that are up to the discretion of the Detrack user to decide what they are used for.
  * Required: sku, desc, qty;
  */
  protected $attributes = [
    "sku" => NULL, //REQUIRED: stock keeping unit or item number.
    "po_no" => NULL,
    "batch_no" => NULL,
    "expiry" => NULL,
    "desc" => NULL, //REQUIRED: desc of the item. If not supplied, an empty sting will be given to the API.
    "cmts" => NULL,
    "qty" => NULL, //REQUIRED: quantity of the item present in the delivery. If not supplied, "1" will be given to the API.
    "uom" => NULL,
  ];
  /**
  * Required attributes are defined here
  */
  protected static $requiredAttributes = ["sku","desc","qty"];

}

 ?>
