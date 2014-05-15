<?php
namespace Nitronet;

class FwkDocumentationDataSource extends FwkPackagesDataSource
{
    protected $buildDir;

    public function __construct($fwkBuildDir)
    {
        $this->buildDir = $fwkBuildDir;
    }

    public function doc($pageName, $version = "master")
    {
        $version = (empty($version) ? 'master' : trim($version));
    }
}