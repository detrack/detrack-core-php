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
 * @property int      $quantity              quantity of item. will be shown on Detrack Proof of Delivery app.
 * @property string   $unit_of_measure       arbitary field, must be enabled in Job Settings to show up in the Detrack Dashboard
 * @property bool     $checked               arbitary field, must be enabled in Job Settings to show up in the Detrack Dashboard
 * @property int      $actual_quantity
 * @property int      $inbound_quantity
 * @property string   $unload_time_estimate
 * @property string   $unload_time_actual
 * @property int      $follow_up_quantity
 * @property string   $follow_up_reason
 * @property int      $rework_quantity
 * @property string   $rework_reason
 * @property int      $reject_quantity
 * @property string   $reject_reason
 * @property float    $weight                arbitary field, must be enabled in Job Settings to show up in the Detrack Dashboard
 * @property string[] $serial_numbers
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
    /**
     * Required attributes are defined here.
     */
    protected static $requiredAttributes = ['sku', 'description', 'quantity'];
}
