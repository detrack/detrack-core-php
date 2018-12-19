<?php

namespace Detrack\DetrackCore\Resource;

use Detrack\DetrackCore\Client\DetrackClientStatic;

class Vehicle extends Resource
{
    /**
     * Attributes a job resource has.
     * Not all of these attributes are compulsory. Required values are to be specified in the $requiredAttributes static variable
     * Fields marked REQUIRED are required by the Detrack API for the most basic functionality.
     * Fields marked OPTIONAL are optional but still utilised by the Detrack Backend System if you supply them.
     * Fields marked EXTRA (or not marked) are arbitary custom fields that are up to the discretion of the Detrack user to decide what they are used for.
     */
    protected $attributes = [
        'id' => null, //REQUIRED (other than create): the id used to identify the driver, tied to the organisation. this id is deleted together with the vehicle.
        'detrack_id' => null, //REQUIRED (create only): the unique id for the person driving. tied to the person, NOT the organisation, to allow him to drive for different organisations. this id persists even after the vehicle is deleted.
        'name' => null, //REQUIRED: the name used by the organisation to alias the driver
        'os_and_version' => null,
        'app_version' => null,
        'speed_limit' => null,
        'stationary_limit' => null,
        'groups' => null,
        'disabled' => null,
        'can_grab_job' => null,
        'mobile_number' => null,
        'zones' => null,
        'status' => null,
        'speed' => null,
        'max_speed' => null,
        'avg_speed' => null,
        'distance' => null,
        'battery' => null,
        'gps' => null,
        'lat' => null,
        'lng' => null,
        'address' => null,
        'connected_at' => null,
        'tracked_at' => null,
        'connection' => null,
        'checked_in_at' => null,
        'created_at' => null,
        'route' => null,
        'heading_to_address' => null,
        'heading_to' => null,
        'last_pod_at' => null,
        'route' => null,
        'time' => null,
        'job' => null,
    ];

    /**
     * Given the id, vehicle name or the detrack_id, fill up the rest of the attributes.
     *
     * @return Vehicle the vehicle with filled up attributes
     */
    public function hydrate()
    {
        if ($this->id == null) {
            //a list of other primary keys we can use to search
            $otherKeys = ['name', 'detrack_id'];
            foreach ($otherKeys as $otherKey) {
                if ($this->$otherKey != null) {
                    $searchResults = Vehicle::listVehicles(['limit' => 9001, $otherKey => $this->$otherKey]);
                    if (count($searchResults) != 0) {
                        foreach ($searchResults as $searchResult) {
                            if ($searchResult->$otherKey == $this->$otherKey) {
                                $this->attributes = json_decode(json_encode($searchResult), true);
                                $this->resetModifiedAttributes();

                                return $this;
                            }
                        }
                    }
                }
            }
        } else {
            $actionPath = 'vehicles/'.$this->id;
            $verb = 'GET';
            $response = DetrackClientStatic::sendData($verb, $actionPath, null);
            if (isset($response->data)) {
                foreach ($response->data as $key => $value) {
                    $this->$key = $value;
                }
                $this->modifiedAttributes = [];

                return $this;
            } elseif (isset($response->code) && $response->code == 'not_found') {
                return null;
            }
        }
    }

    /**
     * List vehicles in a given search condition.
     *
     * @param array $args search arguments in an associative array. Arguments are page, limit, sort, name, detrack_id, zone, group_id
     */
    public static function listVehicles(array $args)
    {
        $actionPath = 'vehicles';
        $verb = 'GET';
        $response = DetrackClientStatic::sendData($verb, $actionPath, $args);
        if (isset($response->data)) {
            $returnArray = [];
            foreach ($response->data as $responseData) {
                $newVehicle = new Vehicle($responseData);
                $newVehicle->modifiedAttributes = [];
                array_push($returnArray, $newVehicle);
            }

            return $returnArray;
        }
    }

    /**
     * Saves the vehicle.
     *
     * @throws Exception Missing required attributes (name, detrack_id)
     *
     * @return Vehicle the new vehicle
     */
    public function save()
    {
        if ($this->id == null) {
            //try to hydrate and find the id
            //if found, it means we are going to perform an update
            //if still null, means we are going to perform an insert
            $returnVehicle = $this->hydrate();
            if ($returnVehicle == null) {
                return $this->create()->resetModifiedAttributes();
            } else {
                $this->id = $returnVehicle->id;

                return $returnVehicle->update()->resetModifiedAttributes();
            }
        } else {
            return $this->update()->resetModifiedAttributes();
        }
    }

    /**
     * Creates the vehicle - performs a strict insert (if it already exists, throw an exception).
     *
     * @throws Exception if the current vehicle object have missing fields
     * @throws Exception if the vehicle contains conflicting name or detrack_id
     *
     * @return Vehicle the newly created vehicle
     */
    public function create()
    {
        $requiredAttributes = ['name', 'detrack_id'];
        foreach ($requiredAttributes as $requiredAttribute) {
            if ($this->$requiredAttribute == null) {
                throw new \Exception('Missing attribute: '.$requiredAttribute);
            }
        }
        $actionPath = 'vehicles';
        $verb = 'POST';
        $validFields = ['detrack_id', 'name', 'speed_limit', 'stationary_limit', 'groups', 'can_grab_job', 'mobile_number', 'zones'];
        $data = json_decode(json_encode(array_filter(array_filter($this->attributes, function ($key) use ($validFields) {
            return in_array($key, $validFields);
        }, ARRAY_FILTER_USE_KEY))));
        $response = DetrackClientStatic::sendData($verb, $actionPath, $data);
        if (isset($response->data)) {
            foreach ($response->data as $key => $value) {
                $this->$key = $value;
            }

            return $this;
        } elseif (isset($response->code) && $response->code == 'validation_failed') {
            throw new \Exception('Validation failed: '.implode(', ', array_map(function ($error) {
                return $error->field.implode('& ', $error->codes);
            }, $response->errors)));
        } elseif (isset($response->code)) {
            throw new \Exception('API Error: ');
        } else {
            throw new \Exception('Something broke');
        }
    }

    /**
     * Updates the vehicle - performs a strict update (if it does not exist, throw an exception).
     *
     * @throws Exception if the current vehicle object has missing fields
     * @throws Exception if the vehicle contains conflicting name or detrack_id
     *
     * @return Vehicle the newly updated vehicle
     */
    public function update()
    {
        if ($this->id == null) {
            $this->attributes = json_decode(json_encode($this->hydrate()), true);
            if ($this->id == null) {
                throw new Exception('The vehicle with the said id/name/detrack_id cannot be found');
            }
        }
        $actionPath = 'vehicles/'.$this->id;
        $verb = 'PUT';
        $validFields = ['detrack_id', 'name', 'speed_limit', 'stationary_limit', 'groups', 'can_grab_job', 'mobile_number', 'zones'];
        $data = json_decode(json_encode(array_filter(array_filter($this->attributes, function ($key) use ($validFields) {
            return in_array($key, $validFields);
        }, ARRAY_FILTER_USE_KEY))));
        $response = DetrackClientStatic::sendData($verb, $actionPath, $data);
        if (isset($response->data)) {
            foreach ($response->data as $key => $value) {
                $this->$key = $value;
            }

            return $this;
        } elseif (isset($response->code) && $response->code == 'not_found') {
            throw new Exception('Vehicle with id '.$this->id.' could not be found');
        } else {
            throw new Exception('Something broke');
        }
    }

    /**
     * Deletes the current vehicle (remove it from the organisation).
     *
     * @return bool whether the delete was successful or not
     */
    public function delete()
    {
        if ($this->id == null) {
            $this->hydrate();
            if ($this == null) {
                return false;
            }
        }
        $verb = 'DELETE';
        $actionPath = 'vehicles/'.$this->id;
        DetrackClientStatic::sendData($verb, $actionPath, null);
        if (!isset($response->data)) {
            return true;
        } else {
            return false;
        }
    }
}
