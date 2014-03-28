<?php
namespace FwkWWW;

use FwkWWW\Exception;

class InvalidConfigFile extends Exception
{
    public function __construct($configFile, $code = null, 
        \Exception $previous = null
    ) {
        parent::__construct(
            sprintf("Invalid configuration file: %s", $configFile), 
            $code, 
            $previous
        );
    }
}