<?php

namespace Detrack\DetrackCore\Resource;

use Detrack\DetrackCore\Client\DetrackClientStatic;
use Detrack\DetrackCore\Model\ItemCollection;

class Job extends Resource
{
    /**
     * Attributes a job resource has.
     * Not all of these attributes are compulsory. Required values are to be specified in the $requiredAttributes static variable
     * Fields marked REQUIRED are required by the Detrack API for the most basic functionality.
     * Fields marked OPTIONAL are optional but still utilised by the Detrack Backend System if you supply them.
     * Fields marked EXTRA (or not marked) are arbitary custom fields that are up to the discretion of the Detrack user to decide what they are used for.
     */
    protected $attributes = [
        //READONLY
        'id' => null,
        'job_age' => null,
        'geocoded_lat' => null,
        'geocoded_lng' => null,
        'detrack_number' => null,
        'tracking_status' => null,
        'shipper_name' => null,
        'reason' => null,
        'last_reason' => null,
        'received_by_sent_by' => null,
        'note' => null,
        'live_eta' => null,
        'pod_lat' => null,
        'pod_lng' => null,
        'pod_address' => null,
        'info_received_at' => null,
        'head_to_pick_up_at' => null,
        'pick_up_at' => null,
        'scheduled_at' => null,
        'at_warehouse_at' => null,
        'out_for_delivery_at' => null,
        'head_to_delivery_at' => null,
        'cancelled_at' => null,
        'pick_up_failed_count' => null,
        'deliver_failed_count' => null,
        'pick_up_assign_to' => null,
        'pick_up_reason' => null,
        'pod_at' => null,
        'texted_at' => null,
        'called_at' => null,
        'address_tracked_at' => null,
        'arrived_lat' => null,
        'arrived_lng' => null,
        'arrived_address' => null,
        'arrived_at' => null,
        'serial_number' => null,
        'signed_at' => null,
        'photo_1_at' => null,
        'photo_2_at' => null,
        'photo_3_at' => null,
        'photo_4_at' => null,
        'photo_5_at' => null,
        'view_signature_url' => null,
        'view_photo_1_url' => null,
        'view_photo_2_url' => null,
        'view_photo_3_url' => null,
        'view_photo_4_url' => null,
        'view_photo_5_url' => null,
        'signature_file_url' => null,
        'photo_1_file_url' => null,
        'photo_2_file_url' => null,
        'photo_3_file_url' => null,
        'photo_4_file_url' => null,
        'photo_5_file_url' => null,
        'actual_weight' => null,
        'temperature' => null,
        'hold_time' => null,
        'payment_collected' => null,
        'actual_crates' => null,
        'actual_pallets' => null,
        'actual_utilization' => null,
        'attempt' => null,
        'goods_service_rating' => null,
        'driver_rating' => null,
        'customer_feedback' => null,
        'items_count' => null,
        //WRITABLE
        'type' => 'Delivery',
        'primary_job_status' => null,
        'open_to_marketplace' => null,
        'marketplace_offer' => null,
        'start_date' => null,
        'status' => null,
        'job_release_time' => null,
        'job_time' => null,
        'time_window' => null,
        'job_received_date' => null,
        'tracking_number' => null,
        'order_number' => null,
        'job_type' => null,
        'job_sequence' => null,
        'job_fee' => null,
        'address_lat' => null,
        'address_lng' => null,
        'company_name' => null,
        'address_1' => null,
        'address_2' => null,
        'address_3' => null,
        'postal_code' => null,
        'city' => null,
        'state' => null,
        'country' => null,
        'billing_address' => null,
        'deliver_to_collect_from' => null,
        'last_name' => null,
        'phone_number' => null,
        'sender_phone_number' => null,
        'fax_number' => null,
        'instructions' => null,
        'assign_to' => null,
        'notify_email' => null,
        'webhook_url' => null,
        'zone' => null,
        'customer' => null,
        'account_no' => null,
        'job_owner' => null,
        'invoice_number' => null,
        'invoice_amount' => null,
        'payment_mode' => null,
        'payment_amount' => null,
        'group_id' => null,
        'group_name' => null,
        'vendor_name' => null,
        'source' => null,
        'weight' => null,
        'parcel_width' => null,
        'parcel_length' => null,
        'parcel_height' => null,
        'cubic_meter' => null,
        'boxes' => null,
        'cartons' => null,
        'pieces' => null,
        'envelopes' => null,
        'pallets' => null,
        'bins' => null,
        'trays' => null,
        'bundles' => null,
        'rolls' => null,
        'number_of_shipping_labels' => null,
        'attachment_url' => null,
        'carrier' => null,
        'auto_reschedule' => null,
        'eta_time' => null,
        'depot' => null,
        'depot_contact' => null,
        'department' => null,
        'sales_person' => null,
        'identification_number' => null,
        'bank_prefix' => null,
        'run_number' => null,
        'pick_up_from' => null,
        'pick_up_time' => null,
        'pick_up_lat' => null,
        'pick_up_lng' => null,
        'pick_up_address' => null,
        'pick_up_address_1' => null,
        'pick_up_address_2' => null,
        'pick_up_address_3' => null,
        'pick_up_city' => null,
        'pick_up_state' => null,
        'pick_up_country' => null,
        'pick_up_postal_code' => null,
        'pick_up_zone' => null,
        'job_price' => null,
        'insurance_price' => null,
        'insurance_coverage' => null,
        'total_price' => null,
        'payer_type' => null,
        'remarks' => null,
        'service_type' => null,
        'warehouse_address' => null,
        'destination_time_window' => null,
        'door' => null,
        'time_zone' => null,
        'pod_time' => null,
    ];

    /**
     * Constructor function for Job resource.
     *
     * @param mixed $attr
     */
    public function __construct($attr = [])
    {
        //convert array/stdClass to array, and get rid of ''
        $attr = array_filter(json_decode(json_encode($attr), true));
        parent::__construct($attr);
        //initialise items
        if (isset($attr['items'])) {
            if (is_array($attr['items'])) {
                $this->items = new ItemCollection($attr['items']);
            } elseif ($attr['items'] instanceof ItemCollection) {
                $this->items = $attr['items'];
            }
        } else {
            $this->items = new ItemCollection();
        }
    }

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
     * Creates the job - performs a strict insert (if it already exists, throw an \Exception).
     *
     * @throws \Exception if the current vehicle job have missing fields
     * @throws \Exception if the vehicle contains conflicting name or detrack_id
     *
     * @return Job the newly created vehicle
     */
    public function create(): Job
    {
        $requiredAttributes = ['do_number', 'address', 'date'];
        foreach ($requiredAttributes as $requiredAttribute) {
            if ($this->$requiredAttribute == null) {
                throw new \Exception('Missing attribute: '.$requiredAttribute);
            }
        }
        $actionPath = 'jobs';
        $verb = 'POST';
        $validFields = ['type', 'primary_job_status', 'open_to_marketplace', 'marketplace_offer', 'do_number', 'attempt', 'date', 'start_date', 'job_age', 'job_release_time', 'job_time', 'time_window', 'job_received_date', 'tracking_number', 'order_number', 'job_type', 'job_sequence', 'job_fee', 'address_lat', 'address_lng', 'address', 'company_name', 'address_1', 'address_2', 'address_3', 'postal_code', 'city', 'state', 'country', 'billing_address', 'deliver_to_collect_from', 'last_name', 'phone_number', 'sender_phone_number', 'fax_number', 'instructions', 'assign_to', 'notify_email', 'webhook_url', 'zone', 'customer', 'account_no', 'job_owner', 'invoice_number', 'invoice_amount', 'payment_mode', 'payment_amount', 'group_name', 'vendor_name', 'shipper_name', 'source', 'weight', 'parcel_width', 'parcel_length', 'parcel_height', 'cubic_meter', 'boxes', 'cartons', 'pieces', 'envelopes', 'pallets', 'bins', 'trays', 'bundles', 'rolls', 'number_of_shipping_labels', 'attachment_url', 'detrack_number', 'status', 'tracking_status', 'reason', 'last_reason', 'received_by_sent_by', 'note', 'carrier', 'pod_lat', 'pod_lng', 'pod_address', 'address_tracked_at', 'arrived_lat', 'arrived_lng', 'arrived_address', 'arrived_at', 'texted_at', 'called_at', 'serial_number', 'signed_at', 'photo_1_at', 'photo_2_at', 'photo_3_at', 'photo_4_at', 'photo_5_at', 'signature_file_url', 'photo_1_file_url', 'photo_2_file_url', 'photo_3_file_url', 'photo_4_file_url', 'photo_5_file_url', 'actual_weight', 'temperature', 'hold_time', 'payment_collected', 'auto_reschedule', 'actual_crates', 'actual_pallets', 'actual_utilization', 'goods_service_rating', 'driver_rating', 'customer_feedback', 'eta_time', 'live_eta', 'depot', 'depot_contact', 'department', 'sales_person', 'identification_number', 'bank_prefix', 'run_number', 'pick_up_from', 'pick_up_time', 'pick_up_lat', 'pick_up_lng', 'pick_up_address', 'pick_up_address_1', 'pick_up_address_2', 'pick_up_address_3', 'pick_up_city', 'pick_up_state', 'pick_up_country', 'pick_up_postal_code', 'pick_up_zone', 'pick_up_assign_to', 'pick_up_reason', 'info_received_at', 'pick_up_at', 'scheduled_at', 'at_warehouse_at', 'out_for_delivery_at', 'head_to_pick_up_at', 'head_to_delivery_at', 'cancelled_at', 'pod_at', 'pick_up_failed_count', 'deliver_failed_count', 'job_price', 'insurance_price', 'insurance_coverage', 'total_price', 'payer_type', 'remarks', 'items_count', 'service_type', 'warehouse_address', 'destination_time_window', 'door', 'time_zone', 'created_at', 'items'];
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
     * Updates the job - performs a strict update (if it does not exist, throw an \Exception).
     *
     * @throws \Exception if the current job object has missing fields
     * @throws \Exception if the vehicle contains conflicting name or detrack_id
     *
     * @return Job the newly updated job
     */
    public function update(): Job
    {
        if ($this->id == null) {
            $this->attributes = json_decode(json_encode($this->hydrate()), true);
            if ($this->id == null) {
                throw new \Exception('The vehicle with the said id/name/detrack_id cannot be found');
            }
        }
        $actionPath = 'jobs/'.$this->id;
        $verb = 'PUT';
        $validFields = ['type', 'primary_job_status', 'open_to_marketplace', 'marketplace_offer', 'do_number', 'attempt', 'date', 'start_date', 'job_age', 'job_release_time', 'job_time', 'time_window', 'job_received_date', 'tracking_number', 'order_number', 'job_type', 'job_sequence', 'job_fee', 'address_lat', 'address_lng', 'address', 'company_name', 'address_1', 'address_2', 'address_3', 'postal_code', 'city', 'state', 'country', 'billing_address', 'deliver_to_collect_from', 'last_name', 'phone_number', 'sender_phone_number', 'fax_number', 'instructions', 'assign_to', 'notify_email', 'webhook_url', 'zone', 'customer', 'account_no', 'job_owner', 'invoice_number', 'invoice_amount', 'payment_mode', 'payment_amount', 'group_name', 'vendor_name', 'shipper_name', 'source', 'weight', 'parcel_width', 'parcel_length', 'parcel_height', 'cubic_meter', 'boxes', 'cartons', 'pieces', 'envelopes', 'pallets', 'bins', 'trays', 'bundles', 'rolls', 'number_of_shipping_labels', 'attachment_url', 'detrack_number', 'status', 'tracking_status', 'reason', 'last_reason', 'received_by_sent_by', 'note', 'carrier', 'pod_lat', 'pod_lng', 'pod_address', 'address_tracked_at', 'arrived_lat', 'arrived_lng', 'arrived_address', 'arrived_at', 'texted_at', 'called_at', 'serial_number', 'signed_at', 'photo_1_at', 'photo_2_at', 'photo_3_at', 'photo_4_at', 'photo_5_at', 'signature_file_url', 'photo_1_file_url', 'photo_2_file_url', 'photo_3_file_url', 'photo_4_file_url', 'photo_5_file_url', 'actual_weight', 'temperature', 'hold_time', 'payment_collected', 'auto_reschedule', 'actual_crates', 'actual_pallets', 'actual_utilization', 'goods_service_rating', 'driver_rating', 'customer_feedback', 'eta_time', 'live_eta', 'depot', 'depot_contact', 'department', 'sales_person', 'identification_number', 'bank_prefix', 'run_number', 'pick_up_from', 'pick_up_time', 'pick_up_lat', 'pick_up_lng', 'pick_up_address', 'pick_up_address_1', 'pick_up_address_2', 'pick_up_address_3', 'pick_up_city', 'pick_up_state', 'pick_up_country', 'pick_up_postal_code', 'pick_up_zone', 'pick_up_assign_to', 'pick_up_reason', 'info_received_at', 'pick_up_at', 'scheduled_at', 'at_warehouse_at', 'out_for_delivery_at', 'head_to_pick_up_at', 'head_to_delivery_at', 'cancelled_at', 'pod_at', 'pick_up_failed_count', 'deliver_failed_count', 'job_price', 'insurance_price', 'insurance_coverage', 'total_price', 'payer_type', 'remarks', 'items_count', 'service_type', 'warehouse_address', 'destination_time_window', 'door', 'time_zone', 'created_at', 'items'];
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
            throw new \Exception('Vehicle with id '.$this->id.' could not be found');
        } else {
            var_dump($response);
            throw new \Exception('Something broke');
        }
    }

    public function delete()
    {
        $verb = 'DELETE';
        if (isset($this->id) && trim($this->id) !== '') {
            $actionPath = 'jobs/'.$this->id;
        } else {
            $actionPath = 'jobs/'.$this->hydrate()->id;
        }
        $response = DetrackClientStatic::sendData($verb, $actionPath, []);
        if ($response == null) {
            return true;
        } else {
            return false;
        }
    }

    /**
     * Given some attributes already present in the class member, retrieve the rest of the attributes from the server.
     *
     * @return Job a copy of the job object with all attributes filled up
     */
    public function hydrate(): ?Job
    {
        $verb = 'POST';
        $actionPath = 'jobs/search';
        if ($this->id != null) {
            $response = DetrackClientStatic::sendData('GET', 'jobs/'.$this->id, null);
        } elseif ($this->do_number != null) {
            $response = DetrackClientStatic::sendData($verb, $actionPath, array_filter($this->jsonSerialize(), function ($key) {
                return $key == 'do_number';
            }, ARRAY_FILTER_USE_KEY));
        } else {
            return null;
        }
        if (!isset($response->data) || $response->data == []) {
            return null;
        } else {
            if (is_array($response->data)) {
                $newJob = new Job($response->data[0]);
            } else {
                $newJob = new Job($response->data);
            }
            $newJob->resetModifiedAttributes();

            return $newJob;
        }
    }

    /**
     * Reattempts the job.
     *
     * @return Job an updated version of the Job object
     */
    public function reattempt(): Job
    {
        $verb = 'POST';
        if (!isset($this->id) || trim($this->id) == '') {
            $this->id = $this->hydrate()->id;
        }
        $actionPath = 'jobs/reattempt';
        $request = new \stdClass();
        $request->id = $this->id;
        $response = DetrackClientStatic::sendData($verb, $actionPath, $request);
        if ($response->data == []) {
            return null;
        } else {
            $newJob = new Job($response->data[0]);
            $newJob->resetModifiedAttributes();

            return $newJob;
        }
    }

    /**
     * Saves the job export to target.
     *
     * If target is a existing directory, it will save into that directory with the default filename specified by the server.
     * If target is an existing file, it will overwrite that file.
     * If the target is a nonexistent path, it will be treated as a file name and the export will be saved to that full path.
     * If no parameter is passed, the raw data of the file is returned as a string for you to save by yourself.
     * The function returns the full path of the new file if a save target was specified, else it returns the raw file data.
     *
     * @param string $document either 'pod' (default) or 'shipping-label'
     * @param string $format   either 'pdf' (default) or 'tiff'
     * @param string $target   either a dir, filepath or null (default)
     *
     * @return string|bool raw file data if no target was passed, or boolean indicating success status
     */
    public function downloadDoc($document = 'pod', $format = 'pdf', $target = null)
    {
        $jwt = DetrackClientStatic::retrieveJWT();
        $verb = 'GET';
        if (!isset($this->id) || trim($this->id) == '') {
            $this->id = $this->hydrate()->id;
        }
        $actionPath = 'jobs/export/'.$this->id.'.'.$format;
        $response = DetrackClientStatic::sendData($verb, $actionPath, [
            'token' => $jwt,
            'format' => $format,
            'document' => $document,
        ]);
        if (is_dir($target)) {
            preg_match('/^attachment; ?filename="(.*)"$/', $response->getHeader('Content-Disposition')[0], $matches);
            $filename = $matches[1];
            $filename = str_replace(' ', '-', $filename);
            $target = rtrim($target, '/');

            return (bool) file_put_contents($target.DIRECTORY_SEPARATOR.$filename, (string) $response->getBody());
        } elseif (!is_null($target)) {
            return (bool) file_put_contents($target, (string) $response->getBody());
        } elseif (is_null($target)) {
            return (string) $response->getBody();
        } else {
            throw new \Exception('Somehow, you managed to reach unreachable code');
        }
    }

    /**
     * Search jobs with the provided search queries.
     *
     * $args is an associative array with the following keys:
     * 'page','limit','sort','date','type','assign_to','status','do_number'
     * Search terms entered into these keys are evaluated with AND condition
     * $query is an optional string that lets you perform a loose search across all attributes.
     *
     * @param array $args  top-level search arguments
     * @param array $query sub-level search term
     *
     * @return array an array of jobs
     **/
    public static function listJobs($args = [], $query = null): array
    {
        $verb = 'GET';
        $actionPath = 'jobs';
        $topLevelArgs = [
        ];
        $sendData = array_merge($topLevelArgs, ['query' => $query]);
        $response = DetrackClientStatic::sendData($verb, $actionPath, $sendData);
        $returnArray = [];
        foreach ($response->data as $responseData) {
            $newJob = new Job(array_filter(json_decode(json_encode($responseData), true)));
            $newJob->modifiedAttributes = [];
            array_push($returnArray, $newJob);
        }

        return $returnArray;
    }

    /**
     * Bulk creates many jobs at once.
     *
     * @param array an array of Job objects, or an array of Job data arguments
     * @param mixed $jobs
     *
     * @return array a subset of the input array containing jobs that were successfully saved
     */
    public static function createJobs($jobs): array
    {
        $verb = 'POST';
        $actionPath = 'jobs/batch';
        $response = DetrackClientStatic::sendData($verb, $actionPath, $jobs);
        $returnArray = [];
        foreach ($response->data as $responseData) {
            $newJob = new Job(array_filter(json_decode(json_encode($responseData), true)));
            $newJob->modifiedAttributes = [];
            array_push($returnArray, $newJob);
        }

        return $returnArray;
    }

    /**
     * Bulk deletes many jobs at once.
     *
     * @param array an array of Job objects
     * @param mixed $jobs
     *
     * @return array a subset of the input array containing jobs that were NOT successfully deleted
     */
    public static function deleteJobs($jobs): array
    {
        $verb = 'DELETE';
        $actionPath = 'jobs';
        $dataArray = array_filter(array_map(function ($job) {
            $job = is_a($job, self::class) ? $job : (is_array($job) ? new Job($job) : null);
            if ($job == null) {
                return null;
            }
            $id = !is_null($job->id) ? $job->id : (!is_null($job->hydrate()) ? $job->hydrate()->id : null);

            return (object) ['id' => $id];
        }, $jobs));
        $response = DetrackClientStatic::sendData($verb, $actionPath, $dataArray);
        $returnArray = [];
        if (isset($response->errors)) {
            $errorIds = array_map(function ($responseError) {
                return $responseError->id;
            }, $response->errors);
            $returnArray = array_filter($jobs, function ($job) use ($errorIds) {
                $job = is_a($job, self::class) ? $job : (is_array($job) ? new Job($job) : null);
                if ($job == null) {
                    return null;
                }
                $id = !is_null($job->id) ? $job->id : (!is_null($job->hydrate()) ? $job->hydrate()->id : null);

                return in_array($id, $errorIds);
            });
        }

        return $returnArray;
    }

    /**
     * Assigns the current job to the specified Vehicle Object.
     *
     * @param Vehicle the vehicle to assign to
     *
     * @return Job returns itself for method chaining
     */
    public function assignTo(Vehicle $vehicle): Job
    {
        if ($vehicle->name == null) {
            $vehicle = $vehicle->hydrate();
            if ($vehicle == null) {
                return $this;
            } else {
                $this->assign_to = $vehicle->name;
            }
        } else {
            $this->assign_to = $vehicle->name;
        }
        $this->save();

        return $this;
    }

    /**
     * Gets the current Vehicle assigned to the current Job.
     *
     * @return Vehicle|null the vehicle the job has been assigned to, NULL if none
     */
    public function getVehicle(): ?Vehicle
    {
        if ($this->assign_to == null) {
            return null;
        }
        $vehicle = new Vehicle();
        $vehicle->name = $this->assign_to;
        $vehicle->hydrate();

        return $vehicle;
    }
}
