<?php

namespace Detrack\DetrackCore\Resource;

use JsonSerializable;
use Doctrine\Common\Collections\ArrayCollection;

class ItemCollection extends ArrayCollection implements JsonSerializable
{
    /**
     * Return attributes that PHP's json_encode will act on.
     *
     * Because the API will treat values entered as NULL as deleting, we will remove null values except where it was modified
     * Call json_encode on each Item object
     *
     * @return array the model's array attributes
     */
    public function jsonSerialize()
    {
        return $this->toArray();
    }

    /**
     * Defines __toString() magic method for debugging purposes.
     *
     * For now, calls json_encode on itself (and thus jsonSerialize()).
     *
     * @return string String representation of the model
     */
    public function __toString()
    {
        return json_encode($this);
    }
}
