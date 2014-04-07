<?php
namespace FwkWWW\Exceptions;

use FwkWWW\Exception;

class InvalidDataSource extends Exception
{
    public function __construct($sourceName, $reason, $code = null, 
        \Exception $previous = null
    ) {
        parent::__construct(
            sprintf("Invalid datasource '%s': %s", $sourceName, $reason), 
            $code, 
            $previous
        );
    }
}