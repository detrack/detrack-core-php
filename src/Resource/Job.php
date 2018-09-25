<?php

namespace Detrack\DetrackCore\Resource;

use Detrack\DetrackCore\Client\DetrackClientStatic;

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
      'id' => null,
      'type' => 'Delivery', //REQUIRED: either 'Delivery' or 'Collection'
      'deliver_to' => null, //OPTIONAL: The name of the recipient to deliver to. This can be a person’s name e.g. John Tan, a company’s name e.g. ABC Inc., or both e.g. John Tan (ABC Inc.)
      'do_number' => null, //REQUIRED: The delivery order number. This attribute must be unique for this date.
      'date' => null, //REQUIRED: The delivery date. Format: YYYY-MM-DD.
      'address' => null, //REQUIRED: The full address. Always include country name for accurate geocoding results.
      'instructions' => null, //OPTIONAL: Any special delivery instruction for the driver. This will be displayed in the delivery detail view on the app.
      'assign_to' => null, //OPTIONAL: The name of the vehicle to assign this delivery to. This must be spelled exactly the same as your vehicle’s name in your dashboard.
      'notify_email' => null, //OPTIONAL: The email address to send customer-facing delivery updates to. If specified, a delivery notification will be sent to this email address upon successful delivery.
      'webhook_url' => null, //OPTIONAL: The URL to post delivery updates to. Please refer to "Delivery Push Notification" on the our documentation.
      'zone' => null, //OPTIONAL: If you divide your deliveries into zones, then specifying this will help you to easily filter out the deliveries by zones in your dashboard.
      'items' => [], //OPTIONAL: array of items to add to the delivery. Will be changed in constructor.
      'initial_status' => null,
      'open_job' => null,
      'offer' => null,
      'do_number' => null,
      'attempt' => null,
      'start_date' => null,
      'age' => null,
      'sync_time' => null,
      'job_time' => null,
      'time_slot' => null,
      'request_date' => null,
      'tracking_number' => null,
      'order_number' => null,
      'job_type' => null,
      'job_order' => null,
      'job_fee' => null,
      'address_lat' => null,
      'address_lng' => null,
      'address_company' => null,
      'address_1' => null,
      'address_2' => null,
      'address_3' => null,
      'postal_code' => null,
      'city' => null,
      'state' => null,
      'country' => null,
      'billing_address' => null,
      'contact_name' => null,
      'contact_last_name' => null,
      'contact_phone' => null,
      'sender_phone' => null,
      'fax' => null,
      'customer' => null,
      'account_no' => null,
      'owner_name' => null,
      'invoice_number' => null,
      'invoice_amount' => null,
      'payment_mode' => null,
      'payment_amount' => null,
      'group_name' => null,
      'vendor_name' => null,
      'shipper_name' => null,
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
      'labels' => null,
      'attachment_1' => null,
      'detrack_number' => null,
      'status' => null,
      'tracking_status' => null,
      'reason' => null,
      'last_reason' => null,
      'handled_by' => null,
      'note' => null,
      'carrier' => null,
      'pod_lat' => null,
      'pod_lng' => null,
      'pod_address' => null,
      'address_tracked_at' => null,
      'arrived_lat' => null,
      'arrived_lng' => null,
      'arrived_address' => null,
      'arrived_at' => null,
      'texted_at' => null,
      'called_at' => null,
      'serial_number' => null,
      'signed_at' => null,
      'photo_1_at' => null,
      'photo_2_at' => null,
      'photo_3_at' => null,
      'photo_4_at' => null,
      'photo_5_at' => null,
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
      'reschedule' => null,
      'actual_crates' => null,
      'actual_pallets' => null,
      'actual_utilization' => null,
      'goods_service_rating' => null,
      'driver_rating' => null,
      'feedback_remarks' => null,
      'eta_time' => null,
      'live_eta' => null,
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
      'pick_up_assign_to' => null,
      'pick_up_reason' => null,
      'info_received_at' => null,
      'pick_up_at' => null,
      'scheduled_at' => null,
      'at_warehouse_at' => null,
      'out_for_delivery_at' => null,
      'head_to_pick_up_at' => null,
      'head_to_delivery_at' => null,
      'cancelled_at' => null,
      'pod_at' => null,
      'pick_up_failed_count' => null,
      'deliver_failed_count' => null,
      'job_price' => null,
      'insurance_price' => null,
      'insured' => null,
      'total_price' => null,
      'payer_type' => null,
      'remarks' => null,
      'items_count' => null,
      'service_type' => null,
      'warehouse_address' => null,
      'destination_timeslot' => null,
      'door' => null,
      'time_zone' => null,
      'created_at' => null,
    ];

    /**
     * Constructor function for Job resource.
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
        $verb = 'POST';
        $actionPath = 'jobs';
        $response = DetrackClientStatic::sendData($verb, $actionPath, $this->jsonSerialize());
        $this->attributes = array_filter(json_decode(json_encode($response->data), true));
        if (isset($response->data->items) && json_decode(json_encode($response->data->items), true) != []) {
            $this->attributes['items'] = new ItemCollection(json_decode(json_encode($response->data->items), true));
        }
        $this->resetModifiedAttributes();

        return $response;
    }

    public function delete()
    {
        $verb = 'DELETE';
        if (isset($this->id) && trim($this->id) !== '') {
            $actionPath = 'jobs/'.$this->id;
        } else {
            $actionPath = 'jobs/'.$this->get()->id;
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
     * @param array $attr optional parameter to indicate only these attributes should be use to search for the Job
     *
     * @return Job a copy of the job object with all attributes filled up
     */
    public function get()
    {
        $this->resetModifiedAttributes();
        $verb = 'POST';
        $actionPath = 'jobs/search';
        $response = DetrackClientStatic::sendData($verb, $actionPath, $this->jsonSerialize());
        if ($response->data == []) {
            return null;
        } else {
            $this->attributes = array_filter(json_decode(json_encode($response->data[0]), true));

            return $this;
        }
    }
}
