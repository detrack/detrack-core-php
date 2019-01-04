<?php

namespace Detrack\DetrackCore\Resource;

use Detrack\DetrackCore\Client\DetrackClientStatic;

/**
 * Represents a driver attached to an organisation in the Detrack ecosystem.
 *
 * @property-read string $id the primary key id of the vehicle, unique to both the driver and the organisation it is attached to.
 * @property-read string $detrack_id the id unique to the person driving.
 * @property string $name the name used by the organisation to identify the driver.
 * @property-read string $os_and_version the operating system version the driver is using
 * @property-read string $app_version the version of the Detrack Driver app the driver is using
 * @property int    $speed_limit      how fast the driver is allowed to travel before an alert is triggered in the Dashboard
 * @property int    $stationary_limit how long, in minutes, the driver is allowed to idle for before an alert is triggered in the Dashboard
 * @property array  $groups           delivery groups to assign this driver to. Pass an array of objects each containing an `id` attribute.
 * @property bool   $can_grab_job     determines whether the driver can compete for jobs in the marketplace
 * @property string $mobile_number    driver's phone number
 * @property-read bool   $disabled         temporarily prevents the driver from accepting jobs or submitting PODs
 * @property array  $zones  array of strings representing the zones this driver is attached to
 * @property string $status status of the vehicle: `"disabled"`, `"installed"`, `"off"`, `"speeding"`, `"stationary"`, `"normal"`
 * @property-read float $speed current speed of the vehicle
 * @property-read float $max_speed highest speed of the vehicle recorded since checking in
 * @property-read float $avg_speed average speed of the vehicle recorded since checking in
 * @property-read float $distance distance travelled by the vehicle since checking in
 * @property-read float $battery the amount of battery remaining on the device used to track the driver
 * @property-read bool $gps whether the driver's device currently has gps signal
 * @property-read float $lat last-known coordinates of the driver (latitude)
 * @property-read float $lng last-known coordinates of the driver (longitude)
 * @property-read string $address last-known address of the driver, if it can be resolved
 * @property-read string $connected_at the last time the driver's device is connected to our servers, in [ISO8601](http://en.wikipedia.org/wiki/ISO_8601) format
 * @property-read string $tracked_at the last time the driver's device received a gps signal and sent us the coordinates, in [ISO8601](http://en.wikipedia.org/wiki/ISO_8601) format
 * @property-read string $connection the connection status: `"off"`, `"no-gps"`, `"on"`
 * @property-read string $checked_in_at when the driver checked in, in [ISO8601](http://en.wikipedia.org/wiki/ISO_8601) format
 * @property-read string $created_at when this vehicle was registered with the organisation for the first time, in [ISO8601](http://en.wikipedia.org/wiki/ISO_8601) format
 * @property-read array $route a array of strings each representing a set of coordinates indicating the route taken for the day encoded in [Google Polyline](https://developers.google.com/maps/documentation/utilities/polylinealgorithm) format **requires route parameter**
 * @property-read string $heading_to_address the address the driver is heading to. The driver must have manually set his next destination in the Detrack Driver App
 * @property-read string $last_pod_at the last time the driver has submitted a POD, in [ISO8601](http://en.wikipedia.org/wiki/ISO_8601) format. "Display vehicle's last POD" **must** be enabled in the Detrack Dashboard Vehicle Settings.
 * @property-read object $heading_to object version of heading to address. `route` is the suggested route in [Google Polyline](https://developers.google.com/maps/documentation/utilities/polylinealgorithm) format from the driver's current position to the current heading_to job, `time` is ???, and `job` contains heading_to job info. **requires route parameter**
 *
 * @todo Model groups
 * @todo Model routes
 */
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
     * Searches for the first vehicle that matches one of the following attributes in order: by `id`, by `name` and by the `detrack_id`. <br>
     * This function is destructive only if a match is found. If no match is found, the function returns null, but the original object is not modified.
     *
     * @chainable
     * @destructive
     * @netcall 1
     *
     * @return $this|null the vehicle with filled up attributes, null if not found
     */
    public function hydrate(): ?Vehicle
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
                    } else {
                        return null;
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
     * The `$args` argument is an associative array containing the following keys:
     *
     * <ul>
     * <li><em>page</em> <code>int</code> page number</li>
     * <li><em>limit</em> <code>int</code> number of records to retrieve</li>
     * <li><em>sort</em> <code>string</code> key to sort by. Add minus sign in front to flip the order. E.g. <code>"-date"</code></li>
     * <li><em>name</em> <code>string</code> vehicle name</li>
     * <li><em>detrack_id</em> <code>string</code> driver's detrack id</li>
     * <li><em>zone</em> <code>string</code> filter service zones</li>
     * <li><em>group_id</em> <code>string</code> filter vehicle group ids</li>
     * </ul>
     *
     * @netcall 1
     *
     * @param array $args search arguments in an associative array. Arguments are page, limit, sort, name, detrack_id, zone, group_id
     *
     * @return array search results as an array of `Vehicle`s
     */
    public static function listVehicles(array $args): array
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
     * This function performs an UPSERT function – it first checks if the Vehicle already exists in the database using the `hydrate();` and calls `update()` if it exists, otherwise calls `create()`.
     *
     * @chainable
     * @destructive
     * @netcall 1 if the `id` property is already set
     * @netcall 2 if the `id` property is not set
     *
     * @throws \Exception Missing required attributes (name, detrack_id)
     *
     * @see Vehicle::hydrate() the hydrate function
     * @see Vehicle::create() the create function
     * @see Vehicle::update() the update function
     *
     * @return $this the new vehicle
     */
    public function save(): Vehicle
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
     * @chainable
     * @destructive
     * @netcall 1
     *
     * @throws \Exception if the current vehicle object is missing the `name` or `detrack_id` field
     * @throws \Exception if the vehicle contains a `name` or `detrack_id` that conflicts with an existing vehicle in the organisation
     *
     * @return $this the newly created vehicle
     */
    public function create(): Vehicle
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
     * @chainable
     * @destructive
     * @netcall 1 if `id` of the `Vehicle` object is already set
     * @netcall 2 if `id` of the `Vehicle` is not yet set
     *
     * @throws \Exception if the current vehicle object has missing fields
     * @throws \Exception if the vehicle with the given id/name/detrack_id cannot be found
     *
     * @see Vehicle::hydrate() the hydrate function is called if the `id` of the `Vehicle` is not yet set
     *
     * @return $this the newly updated vehicle
     */
    public function update(): Vehicle
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
     * @netcall 1 if the `id` of the `Vehicle` is already set
     * @netcall 2 if the `id` of the `Vehicle` is not yet set, calls the Detrack\DetrackCore\Resource\Vehicle::hydrate function
     *
     * @return bool whether the delete was successful or not
     */
    public function delete(): bool
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
