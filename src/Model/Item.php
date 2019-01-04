<?php

namespace Detrack\DetrackCore\Model;

use Detrack\DetrackCore\Resource\Resource;

/**
 * Represents a line item belonging to each Job.
 *
 * While this class extends \Detrack\DetrackCore\Resource\Resource, it is filed under the \Detrack\DetrackCore\Model namespace
 * because there are no special endpoints for items in the Detrack API. This class is made for formalities and convenience
 * when abstracting items in a \Detrack\DetrackCore\Resource\Job object.
 *
 * @property string   $sku                   stock keeping unit. required.
 * @property string   $description           description of item. required if `$sku` is blank
 * @property string   $purchase_order_number arbitary field, must be enabled in Job Settings to show up in the Detrack Dashboard
 * @property string   $batch_number          arbitary field, must be enabled in Job Settings to show up in the Detrack Dashboard
 * @property string   $expiry_date           arbitary field, must be enabled in Job Settings to show up in the Detrack Dashboard
 * @property string   $comments              arbitary field, must be enabled in Job Settings to show up in the Detrack Dashboard
 * @property int      $quantity              quantity of item. will be shown on Detrack Proof of Delivery app to the driver.
 * @property string   $unit_of_measure       arbitary field, must be enabled in Job Settings to show up in the Detrack Dashboard
 * @property bool     $checked               arbitary field, must be enabled in Job Settings to show up in the Detrack Dashboard
 * @property int      $actual_quantity       the actual quantity of item delivered to the customer as indicated by the Driver in the Detrack Proof of Delivery App. If the Driver submits POD without manually filling up this field, it will be set to be equal to the `$qty` field. Must be enabled under Job Settings to show up in the Detrack Dashboard.
 * @property int      $inbound_quantity      arbitary field, must be enabled in Job Settings to show up in the Detrack Dashboard
 * @property string   $unload_time_estimate  arbitary field, must be enabled in Job Settings to show up in the Detrack Dashboard
 * @property string   $unload_time_actual    arbitary field, must be enabled in Job Settings to show up in the Detrack Dashboard
 * @property int      $follow_up_quantity    arbitary field, must be enabled in Job Settings to show up in the Detrack Dashboard
 * @property string   $follow_up_reason      arbitary field, must be enabled in Job Settings to show up in the Detrack Dashboard
 * @property int      $rework_quantity       arbitary field, must be enabled in Job Settings to show up in the Detrack Dashboard
 * @property string   $rework_reason         arbitary field, must be enabled in Job Settings to show up in the Detrack Dashboard
 * @property int      $reject_quantity       quantity of rejects of this item if the Driver has indicated that the customer has rejected some or all of the item in the Detrack Proof of Delivery App
 * @property string   $reject_reason         reason given by the Driver in the Detrack Proof of Delivery App for the customer rejecting items. Valid values are `"Wrong Type"`, `"Wrong Size"`, `"Wrong Color"`, `"Wrong Quantity"`, `"Wrong Item"`, `"Goods Damaged"`, `"Did Not Order"` or `"Others"`.
 * @property float    $weight                arbitary field, must be enabled in Job Settings to show up in the Detrack Dashboard
 * @property string[] $serial_numbers        array of strings representing the data contained in the barcodes/qr codes the driver has scanned using the Detrack Proof of Delivery App
 * @property-read string $photo_url URL of the photo proof of the individual driver taken by the Detrack Proof of Deliery App
 */
class Item extends Resource
{
    /**
     * Attributes an item model has.
     * Not all of these attributes are compulsory.
     * Fields marked REQUIRED are required by the Detrack API for the most basic functionality.
     * Fields marked OPTIONAL are optional but still utilised by the Detrack Backend System if you supply them.
     * Fields marked EXTRA (or not marked) are arbitary custom fields that are up to the discretion of the Detrack user to decide what they are used for.
     * Required: sku, desc, qty;.
     */
    protected $attributes = [
        'id' => null,
        'sku' => null,
        'purchase_order_number' => null,
        'batch_number' => null,
        'expiry' => null,
        'description' => null,
        'comments' => null,
        'quantity' => null,
        'unit_of_measure' => null,
        'checked' => null,
        'actual_quantity' => null,
        'inbound_quantity' => null,
        'unload_time_estimate' => null,
        'unload_time_actual' => null,
        'follow_up_quantity' => null,
        'follow_up_reason' => null,
        'rework_quantity' => null,
        'rework_reason' => null,
        'reject_quantity' => null,
        'reject_reason' => null,
        'weight' => null,
        'serial_numbers' => null,
        'photo_url' => null,
    ];
}
