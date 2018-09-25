<?php

namespace Detrack\DetrackCore\Resource\Model;

use Detrack\DetrackCore\Resource\Resource;

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
