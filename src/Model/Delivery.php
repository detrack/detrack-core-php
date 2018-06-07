<?php

namespace Detrack\DetrackCore\Model;

use Detrack\DetrackCore\Repository\DeliveryRepository;
use RuntimeException;

class Delivery extends Model
{
    use DeliveryRepository;
    /**
     * Attributes a delivery model has.
     * Not all of these attributes are compulsory. Required values are to be specified in the $requiredAttributes static variable
     * Fields marked REQUIRED are required by the Detrack API for the most basic functionality.
     * Fields marked OPTIONAL are optional but still utilised by the Detrack Backend System if you supply them.
     * Fields marked EXTRA (or not marked) are arbitary custom fields that are up to the discretion of the Detrack user to decide what they are used for.
     * Required: date, do, address.
     */
    protected $attributes = [
      'deliver_to' => null, //OPTIONAL: The name of the recipient to deliver to. This can be a person’s name e.g. John Tan, a company’s name e.g. ABC Inc., or both e.g. John Tan (ABC Inc.)
      'delivery_time' => null, //OPTIONAL: The delivery time window. This will be displayed in the job list view and the delivery detail view on the app.
      'status' => null,
      'open_job' => null,
      'offer' => null,
      'do' => null, //REQUIRED: The delivery order number. This attribute must be unique for this date.
      'date' => null, //REQUIRED: The delivery date. Format: YYYY-MM-DD.
      'start_date' => null,
      'sync_time' => null,
      'time' => null,
      'time_slot' => null,
      'req_date' => null,
      'track_no' => null,
      'order_no' => null,
      'job_type' => null,
      'job_order' => null,
      'job_fee' => null,
      'address' => null, //REQUIRED: The full address. Always include country name for accurate geocoding results.
      'addr_company' => null,
      'addr_1' => null,
      'addr_2' => null,
      'addr_3' => null,
      'postal_code' => null,
      'city' => null,
      'state' => null,
      'country' => null,
      'billing_add' => null,
      'name' => null,
      'phone' => null, // OPTIONAL: The phone number of the recipient. If specified, the driver can call the recipient directly from the app.
      'sender_phone' => null,
      'fax' => null,
      'instructions' => null, //OPTIONAL: Any special delivery instruction for the driver. This will be displayed in the delivery detail view on the app.
      'assign_to' => null, //OPTIONAL: The name of the vehicle to assign this delivery to. This must be spelled exactly the same as your vehicle’s name in your dashboard.
      'notify_email' => null, //OPTIONAL: The email address to send customer-facing delivery updates to. If specified, a delivery notification will be sent to this email address upon successful delivery.
      'notify_url' => null, //OPTIONAL: The URL to post delivery updates to. Please refer to "Delivery Push Notification" on the our documentation.
      'zone' => null, //OPTIONAL: If you divide your deliveries into zones, then specifying this will help you to easily filter out the deliveries by zones in your dashboard.
      'customer' => null,
      'acc_no' => null,
      'owner_name' => null,
      'invoice_no' => null,
      'invoice_amt' => null,
      'pay_mode' => null,
      'pay_amt' => null,
      'group_name' => null,
      'src' => null,
      'wt' => null,
      'cbm' => null,
      'boxes' => null,
      'cartons' => null,
      'pcs' => null,
      'envelopes' => null,
      'pallets' => null,
      'bins' => null,
      'trays' => null,
      'bundles' => null,
      'att_1' => null,
      'depot' => null,
      'depot_contact' => null,
      'sales_person' => null,
      'identification_no' => null,
      'bank_prefix' => null,
      'reschedule' => null,
      'pod_at' => null,
      'reason' => null,
      'ITEM-LEVEL' => null,
      'sku' => null,
      'po_no' => null,
      'batch_no' => null,
      'expiry' => null,
      'desc' => null,
      'cmts' => null,
      'qty' => null,
      'uom' => null,
      'detrack_no' => null,
      'attempt' => null,
      'run_no' => null,
      'remarks' => null,
      'items' => [], //OPTIONAL: array of items to add to the delivery. Will be changed in constructor.
    ];
    /**
     * Required attributes are defined here.
     */
    protected static $requiredAttributes = ['date', 'do', 'address'];
    /**
     * Define error code constants returned by the API when calling delivery endpoints.
     *
     * Why are these defined here and not in DeliveryRepository, you ask?
     * IDK, ask PHP why I can't define constants in traits.
     */
    const ERROR_CODE_INVALID_ARGUMENT = '1000';
    const ERROR_CODE_INVALID_KEY = '1001';
    const ERROR_CODE_DELIVERY_ALREADY_EXISTS = '1002';
    const ERROR_CODE_DELIVERY_NOT_FOUND = '1003';
    const ERROR_CODE_DELIVERY_NOT_EDITABLE = '1004';
    const ERROR_CODE_DELIVERY_NOT_DELETABLE = '1005';

    /**
     * Constructor function for Delivery model.
     */
    public function __construct($attr = [], $client = null)
    {
        //convert array/stdClass to array, and get rid of ''
        $attr = array_filter(json_decode(json_encode($attr), true));
        parent::__construct($attr, $client);
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
     * Get the unqiue idenitifier of the delivery object used to find the delivery object in the database.
     *
     * Use this together with the find() function
     *
     * @return array an associative array with indexes "date" and "do"
     */
    public function getIdentifier()
    {
        return array_filter(['date' => $this->date, 'do' => $this->do]);
    }

    /**
     * Returns a binary string representation of the specified POD image.
     *
     * @param int $no Which image file (1-5) to download
     *
     * @throws RuntimeException if param is not an integer from 1 to 5
     *
     * @return string the POD image file, NULL if not found
     */
    public function getPODImage($no)
    {
        $no = (int) $no;
        if (!is_int($no) || $no < 1 || $no > 5) {
            throw new \RuntimeException('POD Image Number must be between 1 to 5');
        }
        try {
            $response = (string) $this->client->sendData('deliveries/photo_'.$no.'.json', $this->getIdentifier())->getBody();

            return $response;
        } catch (\GuzzleHttp\Exception\ClientException $ex) {
            //we got 404'd
            return null;
        }
    }

    /**
     * Downloads and saves an image of the proof of delivery to the path (with filename) specified.
     *
     * This automatically saves the file to disk. If you want to do some image editing before saving, please use getPODImage instead.
     *
     * @param int    $no   Which image file (1-5) to download
     * @param string $path the path you want to download the file to (including the name)
     *
     * @throws Exception        if the path is not writable
     * @throws RuntimeException if there is no POD image with the specified index on the server, or the delivery is not found
     *
     * @return bool returns true if the download is successful
     */
    public function downloadPODImage($no, $path)
    {
        $img = $this->getPODImage($no);
        if ($img == null) {
            throw new \RuntimeException('POD Image does not exist');
        }
        $folder = substr($path, 0, strrpos($path, DIRECTORY_SEPARATOR));
        if (!file_exists($folder)) {
            if (!mkdir($folder, 0740, true)) {
                throw new Exception('Failed to create directory to store POD');
            }
        }
        if (!is_dir($folder)) {
            throw new Exception('Parent path is not a directory');
        }

        return file_put_contents($path, $img);
    }

    /**
     * Downloads the POD in pdf format. Returns a binary string.
     *
     * This does not automatically save the file to disk. If that's what you're looking for, use downloadPODPDF instead.
     *
     * @return string|null the binary data of the pdf, NULL if no pdf is present
     */
    public function getPODPDF()
    {
        try {
            $response = (string) $this->client->sendData('deliveries/export.pdf', $this->getIdentifier())->getBody();

            return $response;
        } catch (\GuzzleHttp\Exception\ClientException $ex) {
            //we got 404'd
            return null;
        }
    }

    /**
     * Downloads the POD in pdf format and writes it to the given path.
     *
     * This automatically saves to disk. If you only want the file data without saving, use getPODPDF instead.
     *
     * @param string $path The path you want to write to (with filename)
     *
     * @throws Exception        if the path is not writable
     * @throws RuntimeException if there is no POD PDF on the server
     *
     * @return bool if download succeeds
     */
    public function downloadPODPDF($path)
    {
        $response = $this->getPODPDF();
        if (is_null($response)) {
            throw new \RuntimeException('There is no POD PDF available to retrieve for this delivery, or wrong delivery details were given');
        }
        $folder = substr($path, 0, strrpos($path, DIRECTORY_SEPARATOR));
        if (!file_exists($folder)) {
            if (!mkdir($folder, 0740, true)) {
                throw new Exception('Failed to create directory to store POD');
            }
        }
        if (!is_dir($folder)) {
            throw new Exception('Parent path is not a directory');
        }

        return file_put_contents($path, $response);
    }

    /**
     * Assign a driver to this delivery.
     *
     * @param string|Vehicle $driver either the name of the driver or the Vehicle object
     *
     * @return Delivery returns itself for method chaining
     */
    public function assignTo($driver)
    {
        if (is_string($driver)) {
            $this->assign_to = $driver;
        } elseif ($driver instanceof Vehicle) {
            $this->assign_to = $driver->name;
        } else {
            throw new \RuntimeException('Invalid argument passed for driver');
        }

        return $this;
    }

    /**
     * Retrieve driver information.
     *
     * @return Vehicle the vehicle assigned to this delivery
     */
    public function getVehicle()
    {
        if ($this->client == null) {
            throw new \RuntimeException('Client not assigned, cannot find vehicle attached to this delivery');
        }

        return $this->client->findVehicle($this->assign_to);
    }

    /**
     * Retrieve driver information. Alias to getVehicle.
     *
     * @see Delivery::getVehicle the function this function aliases
     *
     * @return Vehicle the vehicle assigned to this delivery
     */
    public function getDriver()
    {
        return $this->getVehicle();
    }

    /**
     * Set the delivery vehicle. Alias to assignTo().
     *
     * @see Delivery::assignTo() the function this function aliases.
     *
     * @param string|Vehicle $driver either the name of the driver or the Vehicle object
     *
     * @return Delivery returns itself for method chaining
     */
    public function setVehicle($driver)
    {
        $this->assignTo($driver);
    }

    /**
     * Set the delivery vehicle. Alias to assignTo().
     *
     * @see Delivery::assignTo() the function this function aliases.
     *
     * @param string|Vehicle $driver either the name of the driver or the Vehicle object
     *
     * @return Delivery returns itself for method chaining
     */
    public function setDriver($driver)
    {
        $this->assignTo($driver);
    }

    /**
     * Gets the entire array of attributes in key=>value format.
     *
     * @see Delivery::$attributes the array this function references
     *
     * @return array an array of attributes in key=>value format
     */
    public function getAttributes()
    {
        return $this->attributes;
    }
}
