<?php

namespace Detrack\DetrackCore\Resource;

use Detrack\DetrackCore\Client\DetrackClientStatic;
use Detrack\DetrackCore\Model\ItemCollection;

/**
 * Represents a Job (either a delivery of a collection) in the Detrack ecosystem.
 *
 * @property-read string $id the unique id used by the Detrack backend to identify the Job
 * @property-read int $job_age the number of days since the Job's scheduled delivery date (same day deliveries count as age 1)
 * @property-read float $geocoded_lat the geocoded latitude of the address. It automatically changes to reflect the coordinates of the current address, however, it is not updated immediately after changing the address as it takes some time for the geocoding service to process the address.
 * @property-read float $geocoded_lng the geocoded longitude of the address. It automatically changes to reflect the coordinates of the current address, however, it is not updated immediately after changing the address as it takes some time for the geocoding service to process the address.
 * @property-read string $detrack_number another id used by some other apps to identify the Job
 * @property-read string $tracking_status user-facing tracking status. Possible values are `"Info received"` (default upon creation), `"Out for delivery"`, `"Completed"`, `"Partially completed"`, `"Failed"`, `"On hold"`, `"Return"`, although more fields can be arbitarily defined in User Profiles. Not to be confused with `$status`.
 * @property-read string $shipper_name MISSING FIELD
 * @property-read string $reason the reason provided by the driver why the Job has failed.
 * @property-read string $last_reason MISSING FIELD
 * @property-read string $received_by_sent_by the name of the person who signed on the POD when the Job has reached its destination
 * @property-read string $note personal note entered by the driver on the Detrack Proof of Delivery app
 * @property-read string $live_eta timestamp in the [ISO8601](http://en.wikipedia.org/wiki/ISO_8601) format that mirrors the `$eta_time` field or a certain offset based on `$eta_time` if it has been configured.
 * @property-read float $pod_lat latitude of position where the pod was taken by the driver **requires "Enable Manual POD" in the Detrack Dashboard Job Settings**
 * @property-read float $pod_lng longitude of position where the pod was taken by the driver **requires "Enable Manual POD" in the Detrack Dashboard Job Settings**
 * @property-read float $pod_address estimated address where the pod was taken by the driver **requires "Enable Manual POD" in the Detrack Dashboard Job Settings**
 * @property-read string $info_received_at MISSING FIELD
 * @property-read string $head_to_pick_up_at MISSING FIELD
 * @property-read string $pick_up_at MISSING FIELD
 * @property-read string $scheduled_at MISSING FIELD
 * @property-read string $at_warehouse_at MISSING FIELD
 * @property-read string $out_for_delivery_at MISSING FIELD
 * @property-read string $head_to_delivery_at MISSING FIELD
 * @property-read string $cancelled_at MISSING FIELD
 * @property-read int $pick_up_failed_count MISSING FIELD
 * @property-read int $deliver_failed_count MISSING FIELD
 * @property-read string $pick_up_assign_to MISSING FIELD
 * @property-read string $pick_up_reason MISSING FIELD
 * @property-read string $pod_at timestamp in [ISO8601](http://en.wikipedia.org/wiki/ISO_8601) format indicating when the POD was submitted
 * @property-read string $texted_at timestamp in [ISO8601](http://en.wikipedia.org/wiki/ISO_8601) format indicating when the driver sent a text message to the recipient
 * @property-read string $called_at timestamp in [ISO8601](http://en.wikipedia.org/wiki/ISO_8601) format indicating when the driver made a phone call to the recipient
 * @property-read string $address_tracked_at NO IDEA
 * @property-read float $arrived_lat gps data indicating where the driver was estimated to be when he indicated in the app he had arrived at his destination
 * @property-read float $arrived_lng gps data indicating where the driver was estimated to be when he indicated in the app he had arrived at his destination
 * @property-read string $arrived_address roughly resolved address indicating where the driver was estimated to be when he indicated in the app he had arrived at his destination.
 * @property-read string $arrived_at timestamp in [ISO8601](http://en.wikipedia.org/wiki/ISO_8601) format indicating when the driver has indicated in the Detrack POD app that he has arrived at the destination
 * @property-read string $serial_number NO IDEA (only when driver has submitted POD??)
 * @property-read string $signed_at timestamp in [ISO8601](http://en.wikipedia.org/wiki/ISO_8601) format indicating when the driver captured the recipient's signature
 * @property-read string $photo_1_at timestamp in [ISO8601](http://en.wikipedia.org/wiki/ISO_8601) format indicating when the driver took the 1st photo in the POD
 * @property-read string $photo_2_at timestamp in [ISO8601](http://en.wikipedia.org/wiki/ISO_8601) format indicating when the driver took the 2nd photo in the POD
 * @property-read string $photo_3_at timestamp in [ISO8601](http://en.wikipedia.org/wiki/ISO_8601) format indicating when the driver took the 3rd photo in the POD
 * @property-read string $photo_4_at timestamp in [ISO8601](http://en.wikipedia.org/wiki/ISO_8601) format indicating when the driver took the 4th photo in the POD
 * @property-read string $photo_5_at timestamp in [ISO8601](http://en.wikipedia.org/wiki/ISO_8601) format indicating when the driver took the 5th photo in the POD
 * @property-read string $view_signature_url MISSING FIELD
 * @property-read string $view_photo_1_url MISSING FIELD
 * @property-read string $view_photo_2_url MISSING FIELD
 * @property-read string $view_photo_3_url MISSING FIELD
 * @property-read string $view_photo_4_url MISSING FIELD
 * @property-read string $view_photo_5_url MISSING FIELD
 * @property-read string $signature_file_url after submitting POD, a URL where you can download the image file of the signature if the Driver has collected one.
 * @property-read string $photo_1_file_url after submitting POD, a URL where you can download photo proof #1 if the Driver has collected one.
 * @property-read string $photo_2_file_url after submitting POD, a URL where you can download photo proof #2 if the Driver has collected one.
 * @property-read string $photo_3_file_url after submitting POD, a URL where you can download photo proof #3 if the Driver has collected one.
 * @property-read string $photo_4_file_url after submitting POD, a URL where you can download photo proof #4 if the Driver has collected one.
 * @property-read string $photo_5_file_url after submitting POD, a URL where you can download photo proof #5 if the Driver has collected one.
 * @property-read string $actual_weight extra field keyed in by the Driver when he submits the POD.
 * @property-read string $temperature extra field keyed in by the Driver when he submits the POD.
 * @property-read string $hold_time extra field keyed in by the Driver when he submits the POD.
 * @property-read string $payment_collected extra field keyed in by the Driver when he submits the POD.
 * @property-read string $actual_crates extra field keyed in by the Driver when he submits the POD.
 * @property-read string $actual_pallets extra field keyed in by the Driver when he submits the POD.
 * @property-read string $actual_utilization extra field keyed in by the Driver when he submits the POD.
 * @property-read int $attempt the nth attempt of the job with the same `do_number`. Indexed at 1.
 * @property-read int $goods_service_rating star rating out of 5 the recipient has given the driver in the Tap to Track widget after receiving the delivery
 * @property-read int $driver_rating star rating out of 5 the recipient has given the driver in the Tap to Track widget after receiving the delivery
 * @property-read string $customer_feedback text feedback the recipient has given the driver in the Tap to Track widget after receiving the delivery
 * @property-read int $items_count number of items added to the job, which is basically counting the number of objects in the `items` array.
 * @property string                                    $type                      denotes the type of Job, either `"Delivery"` (default) or `"Collection"`
 * @property string                                    $deliver_to_collect_from   the name of the recipient to deliver to. The name of the recipient to deliver to. This can be a person’s name e.g. John Tan, a company’s name e.g. ABC Inc., or both e.g. John Tan (ABC Inc.)
 * @property string                                    $do_number                 the main key used to identify Jobs on the Detrack Dashboard. However, take note that multiple Jobs can have the same `do_number` across different dates to represent reattempts.
 * @property string                                    $date                      the delivery date in `"YYYY-MM-DD"` format
 * @property string                                    $address                   the full address where the delivery is headed for. Always include country name for accurate geocoding results.
 * @property string                                    $instructions              any special delivery instructions for the driver that will be displayed on the Detrack Proof of Delivery app.
 * @property string                                    $assign_to                 the name of the vehicle to assign this delivery to
 * @property string                                    $notify_email              the email address where the customer-facing delivery notification will be sent to upon completion of the delivery. Set this only if you wish to send a delivery notification to your customer. Otherwise, leave this field blank.
 * @property string                                    $webhook_url               the url to post delivery updates to. Please refer to [Delivery Push Notification](https://www.detrack.com/api-documentation/delivery-push-notification/) on our documentation for more info.
 * @property string                                    $zone                      zone id to assign this Job to.
 * @property \Detrack\DetrackCore\Model\ItemCollection $items                     array of items to add to the Job. Do not modify this attribute directly.
 * @property string                                    $primary_job_status        CHECK AGAIN
 * @property string                                    $open_to_marketplace       whether this job is available in the marketplace for drivers to grab
 * @property string                                    $marketplace_offer         price at which this job is placed on the marketplace for
 * @property string                                    $start_date                date this job started. Used to calculate `job_age`. If left blank, `date` is used instead.
 * @property string                                    $status                    the functional status of the Job. Can either be `"info_recv"`, `"dispatched"`, `"completed"`, `"completed_partial"`, `"failed"`, `"on_hold"`, or `"return"`.
 * @property string                                    $job_release_time          NO IDEA
 * @property string                                    $job_time                  arbitary field entered in the Detrack Dashboard under "Delivery / Collection time"
 * @property string                                    $time_window               NO IDEA
 * @property string                                    $job_received_date         NO IDEA
 * @property string                                    $tracking_number           arbitary field entered in the Detrack Dashboard under "Tracking #"
 * @property string                                    $order_number              arbitary field entered in the Detrack Dashboard under "Order #"
 * @property string                                    $job_type                  NO IDEA
 * @property string                                    $job_sequence              NO IDEA
 * @property string                                    $job_fee                   NO IDEA
 * @property float                                     $address_lat               lets you manually specify the coordinates of `address`
 * @property float                                     $address_lng               lets you manually specify the coordinates of `address`
 * @property string                                    $company_name              arbitary field entered in the Detrack Dashboard under "Address Company"
 * @property string                                    $address_1                 NO IDEA
 * @property string                                    $address_2                 NO IDEA
 * @property string                                    $address_3                 NO IDEA
 * @property string                                    $postal_code               NO IDEA
 * @property string                                    $city                      NO IDEA
 * @property string                                    $state                     NO IDEA
 * @property string                                    $country                   NO IDEA
 * @property string                                    $billing_address           arbitary field entered in the Detrack Dashboard under "Billing Address"
 * @property string                                    $last_name                 arbitary field entered in the Detrack Dashboard under "Last name"
 * @property string                                    $phone_number              phone number of the recipient. If entered, the driver will be able to call or text the recipient directly from the Detrack POD app.
 * @property string                                    $sender_phone_number       arbitary field entered in the Detrack Dashboard under "Sender phone #"
 * @property string                                    $fax_number                arbitary field entered in the Detrack Dashboard under "Fax #"
 * @property string                                    $customer                  arbitary field entered in the Detrack Dashboard under "Customer"
 * @property string                                    $account_no                arbitary field entered in the Detrack Dashboard under "Account #"
 * @property string                                    $job_owner                 arbitary field entered in the Detrack Dashboard under "Owner name"
 * @property string                                    $invoice_number            arbitary field entered in the Detrack Dashboard under "Invoice #"
 * @property float                                     $invoice_amount            arbitary field entered in the Detrack Dashboard under "Invoice amt"
 * @property string                                    $payment_mode              arbitary field entered in the Detrack Dashboard under "Payment mode"
 * @property string                                    $payment_amount            if specified, tells the Driver to collect said amount of money in the Detrack POD app.
 * @property string                                    $group_id                  id of group if this Job is assigned to one
 * @property string                                    $group_name                name of group if this Job is assigned to one
 * @property string                                    $vendor_name               MISSING FIELD
 * @property string                                    $source                    arbitary field entered in the Detrack Dashboard under "Source"
 * @property float                                     $weight                    arbitary field entered in the Detrack Dashboard under "Weight"
 * @property float                                     $parcel_width              arbitary field entered in the Detrack Dashboard under "Parcel width"
 * @property float                                     $parcel_length             arbitary field entered in the Detrack Dashboard under "Parcel length"
 * @property float                                     $parcel_height             arbitary field entered in the Detrack Dashboard under "Parcel height"
 * @property float                                     $cubic_meter               arbitary field entered in the Detrack Dashboard under "CBM"
 * @property int                                       $boxes                     arbitary field entered in the Detrack Dashboard under "Boxes"
 * @property int                                       $cartons                   arbitary field entered in the Detrack Dashboard under "Cartons"
 * @property int                                       $pieces                    arbitary field entered in the Detrack Dashboard under "Pieces"
 * @property int                                       $envelopes                 arbitary field entered in the Detrack Dashboard under "Envelopes"
 * @property int                                       $pallets                   arbitary field entered in the Detrack Dashboard under "Pallets"
 * @property int                                       $bins                      arbitary field entered in the Detrack Dashboard under "Bins"
 * @property int                                       $trays                     arbitary field entered in the Detrack Dashboard under "Trays"
 * @property int                                       $bundles                   arbitary field entered in the Detrack Dashboard under "Bundles"
 * @property int                                       $rolls                     arbitary field entered in the Detrack Dashboard under "Rolls"
 * @property string                                    $number_of_shipping_labels NO IDEA
 * @property string                                    $attachment_url            optional field where you can specify a URL to an attachment that the driver can view in the Detrack POD app
 * @property string                                    $carrier                   arbitary field entered in the Detrack Dashboard under "Carrier"
 * @property string                                    $auto_reschedule           NO IDEA
 * @property string                                    $eta_time                  NO IDEA
 * @property string                                    $depot                     arbitary field entered in the Detrack Dashboard under "Depot"
 * @property string                                    $depot_contact             arbitary field entered in the Detrack Dashboard under "Depot contact"
 * @property string                                    $department                arbitary field entered in the Detrack Dashboard under "Department"
 * @property string                                    $sales_person              arbitary field entered in the Detrack Dashboard under "Sales person"
 * @property string                                    $identification_number     arbitary field entered in the Detrack Dashboard under "Identification #"
 * @property string                                    $bank_prefix               arbitary field entered in the Detrack Dashboard under "Bank Prefix"
 * @property string                                    $run_number                arbitary field entered in the Detrack Dashboard under "Run #"
 * @property string                                    $pick_up_from              MISSING FIELD
 * @property string                                    $pick_up_time              MISSING FIELD
 * @property float                                     $pick_up_lat               MISSING FIELD
 * @property float                                     $pick_up_lng               MISSING FIELD
 * @property string                                    $pick_up_address           MISSING FIELD
 * @property string                                    $pick_up_address_1         MISSING FIELD
 * @property string                                    $pick_up_address_2         MISSING FIELD
 * @property string                                    $pick_up_address_3         MISSING FIELD
 * @property string                                    $pick_up_city              MISSING FIELD
 * @property string                                    $pick_up_state             MISSING FIELD
 * @property string                                    $pick_up_country           MISSING FIELD
 * @property string                                    $pick_up_postal_code       MISSING FIELD
 * @property string                                    $pick_up_zone              MISSING FIELD
 * @property float                                     $job_price                 arbitary field entered in the Detrack Dashboard under "Job price"
 * @property float                                     $insurance_price           arbitary field entered in the Detrack Dashboard under "Insurance price"
 * @property bool                                      $insurance_coverage        arbitary field entered in the Detrack Dashboard under "Insured"
 * @property float                                     $total_price               arbitary field entered in the Detrack Dashboard under "Total price"
 * @property string                                    $payer_type                NO IDEA
 * @property string                                    $remarks                   arbitary field entered in the Detrack Dashboard under "Remarks"
 * @property string                                    $service_type              arbitary field entered in the Detrack Dashboard under "Service type"
 * @property string                                    $warehouse_address         arbitary field entered in the Detrack Dashboard under "Warehouse address"
 * @property string                                    $destination_time_window   arbitary field entered in the Detrack Dashboard under "Destination timeslot"
 * @property string                                    $door                      arbitary field entered in the Detrack Dashboard under "door"
 * @property string                                    $time_zone                 NO IDEA
 * @property string                                    $pod_time                  time at which POD was submitted, in `HH:MM AM|PM` format.
 */
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
        'do_number' => null,
        'date' => null,
        'address' => null,
        'items' => null,
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

    /**
     * Saves the Job.
     *
     * This function performs an UPSERT function – it first checks if the Job already exists in the database using the `hydrate();` and calls `update()` if it exists, otherwise calls `create()`.
     *
     * @chainable
     * @destructive
     * @netcall 1 if the `id` property is already set
     * @netcall 2 if the `id` property is not set
     *
     * @throws \Exception Missing required attributes (do_number, date, address)
     *
     * @see Job::hydrate() the hydrate function
     * @see Job::create() the create function
     * @see Job::update() the update function
     *
     * @return $this the new job
     */
    public function save(): Job
    {
        if ($this->id == null) {
            //try to hydrate and find the id
            //if found, it means we are going to perform an update
            //if still null, means we are going to perform an insert
            $returnJob = $this->hydrate();
            if ($returnJob == null) {
                return $this->create()->resetModifiedAttributes();
            } else {
                $this->id = $returnJob->id;

                return $returnJob->update()->resetModifiedAttributes();
            }
        } else {
            return $this->update()->resetModifiedAttributes();
        }
    }

    /**
     * Creates the job - performs a strict insert (if it already exists, throw an \Exception).
     *
     * @chainable
     * @destructive
     * @netcall 1
     *
     * @throws \Exception if the current job have missing fields
     * @throws \Exception if the job contains conflicting do_number on the same date
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
     * @chainable
     * @destructive
     * @netcall 1 if the `id` of the `Job` object is already set
     * @netcall 2 if the `id` of the `Job` object is unset, calls \Detrack\DetrackCore\Resource\Job::hydrate
     *
     * @throws \Exception if the current job object has missing fields
     * @throws \Exception if the job contains conflicting do_number on the same date
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

    /**
     * Deletes the job and removes it from the dashboard.  **Completed and Failed Jobs cannot be deleted by default (see full description)**.
     *
     * Under the default dashboard settings, Jobs with a status of `"completed"`, `"completed_partial"`, or `"failed"` cannot be deleted unless you enable the "Enable deletion of completed jobs" setting in the Detrack Dashboard (Settings > Job Settings > POD. Enable deletion of completed jobs)
     *
     * @netcall 1 if the `id` of the `Job` is already set
     * @netcall 2 if the `id` of the `Job` is not yet set, calls the Detrack\DetrackCore\Resource\Job::hydrate function
     *
     * @return bool whether the delete was successful or not
     */
    public function delete(): bool
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
     * @chainable
     * @destructive
     * @netcall 1
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
     * Note that job reattempts have a different `id`, but the same `do_number` and `date` unless you reschedule the reattempt to another day.
     *
     * @chainable
     * @destructive
     * @netcall 1 if `id` of `Job` object is already set
     * @netcall 2 if `id` of `Job` object is unset, calls the \Detrack\DetrackCore\Resource\Job::hydrate() function
     *
     * @return $this an reattempted version of the Job object
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
     * @netcall 1
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
     * @netcall 1
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
     * @netcall 1
     *
     * @param array $jobs an array of Job objects, or an array of Job data arguments
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
     * @netcall 1
     *
     * @param array $jobs an array of Job objects
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
     * This function also calls Detrack\DetrackCore\Resource\Job::save upon assigning the vehicle.
     *
     * @chainable
     * @destructive
     * @netcall 1 if `id` of both `Vehicle` and `Job` is already set
     * @netcall 2 if `id` of either one is set
     * @netcall 3 if `id` of neither is set
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
     * Calls the Detrack\DetrackCore\Resource\Vehicle::hydrate method on the vehicle.
     *
     * @chainable
     * @netcall 1
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
