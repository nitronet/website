<?php
namespace Nitronet;

use Fwk\Core\Components\ViewHelper\ViewHelperService;

class FwkDocumentationDataSource extends FwkPackagesDataSource
{
    protected $buildDir;

    public function __construct($fwkBuildDir)
    {
        $this->buildDir = $fwkBuildDir;
    }

    public function doc($page, $package, $version = "master")
    {
        $version = (empty($version) ? 'master' : trim($version));

        $docFile = $this->buildDir
            . DIRECTORY_SEPARATOR
            . ucfirst(strtolower($package))
            . DIRECTORY_SEPARATOR
            . 'build'
            . DIRECTORY_SEPARATOR
            . $version
            . DIRECTORY_SEPARATOR
            . 'docs'
            . DIRECTORY_SEPARATOR
            . $page .'.json';

        if (!is_file($docFile) || !is_readable($docFile)) {
            throw new \InvalidArgumentException('invalid documentation page: '. $page);
        }

        return json_decode(file_get_contents($docFile));
    }

    public function restoreDocLinks($contents, $package, $version, ViewHelperService $viewHelper)
    {
        if (preg_match_all('#href="DOCKLINK:(.[^\"]*)"#', $contents, $matches)) {
            $finds = array();
            $repl = array();
            foreach ($matches[0] as $idx => $value) {
                $finds[] = $value;
                $repl[] = 'href="'. $viewHelper->url('PageView', array(
                    'page'      => 'fwk/doc',
                    'package'   => $package,
                    'version'   => $version,
                    'docPage'   => $matches[1][$idx]
                )) .'"';
            }

            $contents = str_replace($finds, $repl, $contents);
        }

        return $contents;
    }
}