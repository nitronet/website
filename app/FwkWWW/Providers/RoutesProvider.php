<?php
namespace FwkWWW\Providers;

use FwkWWW\CmsService;

interface RoutesProvider
{
    public function getRoutes(CmsService $cms);
}