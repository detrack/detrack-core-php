<?php

namespace Detrack\DetrackCore\Model;

use Detrack\DetrackCore\Client\DetrackClient;

class Vehicle extends Model{
    /**
    * Attributes a vehicle model has.
    * Not all of these attributes are compulsory.
    * Fields marked REQUIRED are required by the Detrack API for the most basic functionality.
    * Fields marked OPTIONAL are optional but still utilised by the Detrack Backend System if you supply them.
    * Fields marked EXTRA (or not marked) are arbitary custom fields that are up to the discretion of the Detrack user to decide what they are used for.
    * Required: sku, desc, qty;
    *
    * All of these fields are read only.
    */
    protected $attributes = [
      "name" => NULL, //REQUIRED: The vehicle name.
      "detrack_id" => NULL, // The Detrack ID tagged to this vehicle.
      "speed_limit" => NULL, // The speed limit set by the user.
      "stationary_limit" => NULL, // The stationary time limit set by the user.
      "disabled" => NULL, // The status of this vehicle. If vehicle is disabled, the value is false, else it is true.
      "lat" => NULL, // The latitude of the current / last known location.
      "lng" => NULL, // The longitude of the current / last known location.
      "address" => NULL, // The address of the current / last known location.
      "no_gps" => NULL, // The GPS status of this vehicle. If vehicle has GPS signal, the value is false, else it is true.
      "speed" => NULL, // The current speed.
      "max_speed" => NULL, // The maximum speed.
      "avg_speed" => NULL, // The average speed.
      "distance" => NULL, // The distance traveled.
      "tracked_at" => NULL, // The time the vehicle is last tracked. ISO 8601 format: YYYY–MM–DDTHH:MM:SS+HH:MM e.g. 2014-02-13T09:30:45+08:00
    ];
    /**
    * Required attributes are defined here
    */
    protected static $requiredAttributes = ["name"];
}

?>
