<?php
namespace FwkWWW;



class PathUtils
{
    protected $basePath;
    protected $pagesPath;
    protected $configPath;
    
    public function __construct($basePath)
    {
        $this->basePath     = $basePath;
    }
    
    public function calculate(array $parts, $absolute = false)
    {
        $final = array();
        if ($absolute === false) {
            $final[] = rtrim($this->basePath, DIRECTORY_SEPARATOR);
        }
        
        foreach ($parts as $part) {
            $final[] = rtrim($part, DIRECTORY_SEPARATOR);
        }
        
        $path = implode(DIRECTORY_SEPARATOR, $final);
        $realpath = realpath($path);
        if ($realpath === false) {
            throw new Exception("The path '$path' is invalid");
        }
        
        return $realpath;
    }
}
