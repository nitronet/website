<?php
namespace FwkWWW;


interface DataSource
{
    public function fetch(array $options = array());
}