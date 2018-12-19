<?php

namespace Detrack\DetrackCore\Model;

class Vehicle extends Model
{
    /**
     * Attributes a vehicle model has.
     * Not all of these attributes are compulsory.
     * Fields marked REQUIRED are required by the Detrack API for the most basic functionality.
     * Fields marked OPTIONAL are optional but still utilised by the Detrack Backend System if you supply them.
     * Fields marked EXTRA (or not marked) are arbitary custom fields that are up to the discretion of the Detrack user to decide what they are used for.
     * Required: sku, desc, qty;.
     *
     * All of these fields are read only.
     */
    protected $attributes = [
        'name' => null, //REQUIRED: The vehicle name.
        'detrack_id' => null, // The Detrack ID tagged to this vehicle.
        'speed_limit' => null, // The speed limit set by the user.
        'stationary_limit' => null, // The stationary time limit set by the user.
        'disabled' => null, // The status of this vehicle. If vehicle is disabled, the value is false, else it is true.
        'lat' => null, // The latitude of the current / last known location.
        'lng' => null, // The longitude of the current / last known location.
        'address' => null, // The address of the current / last known location.
        'no_gps' => null, // The GPS status of this vehicle. If vehicle has GPS signal, the value is false, else it is true.
        'speed' => null, // The current speed.
        'max_speed' => null, // The maximum speed.
        'avg_speed' => null, // The average speed.
        'distance' => null, // The distance traveled.
        'tracked_at' => null, // The time the vehicle is last tracked. ISO 8601 format: YYYY–MM–DDTHH:MM:SS+HH:MM e.g. 2014-02-13T09:30:45+08:00
    ];
    /**
     * Required attributes are defined here.
     */
    protected static $requiredAttributes = ['name'];
}
