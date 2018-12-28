<?php

namespace Detrack\DetrackCore\Resource;

abstract class Resource implements \JsonSerializable
{
    /**
     * An associative array that stores what values have been updated since the last save() function calls.
     *
     * We will store the names of the stored attributes in keys, not in values because it is faster
     */
    protected $modifiedAttributes = [];
    protected static $requiredAttributes = [];

    public function __construct($attr = [])
    {
        //convert array/stdClass to array, and get rid of ''
        $attr = array_filter(json_decode(json_encode($attr), true));
        foreach ($this->attributes as $key => $value) {
            if (isset($attr[$key])) {
                $this->attributes[$key] = $attr[$key];
                $this->modifiedAttributes[$key] = true;
            }
        }
    }

    public function __get($key)
    {
        return $this->attributes[$key];
    }

    public function __set($key, $value): void
    {
        $this->modifiedAttributes[$key] = true;
        $this->attributes[$key] = $value;
    }

    /**
     * Return attributes that PHP's json_encode will act on.
     *
     * Because the API will treat values entered as NULL as deleting, we will remove null values except where it was modified
     *
     * @return array the model's array attributes
     */
    public function jsonSerialize(): array
    {
        return array_filter($this->attributes, function ($attribute) {
            /*conditions for not getting filtered out:
            * Must not be NULL
            * If NULL, must have been modified to NULL and not initialised by default as NULL
            */
            return !is_null($this->attributes[$attribute]) ? true : (isset($this->modifiedAttributes[$attribute]) || in_array($attribute, static::$requiredAttributes));
        }, ARRAY_FILTER_USE_KEY);
    }

    /**
     * Reset the modifiedAttributes array.
     *
     * @return $this returns itself with the modifiedAttributes array reset
     */
    protected function resetModifiedAttributes(): Resource
    {
        $this->modifiedAttributes = [];

        return $this;
    }

    /**
     * Defines __toString() magic method for debugging purposes.
     *
     * For now, calls json_encode on itself (and thus jsonSerialize()).
     *
     * @return string String representation of the model
     */
    public function __toString(): string
    {
        return json_encode($this);
    }
}
