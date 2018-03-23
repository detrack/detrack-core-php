<?php

namespace Detrack\DetrackCore\Repository\Exception;

class MissingFieldException extends \Exception
{
    public function __construct($object, $missingField, Exception $previous = null)
    {
        $message = 'Field required to save object '.$object.' : '.$missingField;
        parent::__construct($message, 0, $previous);
    }

    public function __toString()
    {
        return __CLASS__.": {$this->message}\n";
    }
}
