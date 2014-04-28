<?php
namespace Nitronet\Utils;

class HttpClient
{
    const METHOD_GET    = 'GET';
    const METHOD_POST   = 'POST';
    
    protected $config = array();
    
    public function __construct(array $config)
    {
        $this->config = array_merge(array(
            'useragent'         => 'nitronet:HttpClient/1.0 php/curl',
            'ssl.verifypeer'    => true,
            'ssl.verifyhost'    => 2,
            'ssl.cainfo'        => null,
            'proxy.hostname'    => null,
            'proxy.port'        => 8080,
            'proxy.username'    => null,
            'proxy.password'    => null,
            'timeout'           => 10
        ), $config);
    }
    
    public function get($url)
    {
        $curl = $this->curlFactory($url, self::METHOD_GET);
        $result = curl_exec($curl);
        $err    = curl_error($curl);
        
        if (!empty($err)) {
            throw new \RuntimeException('cURL Error: '. $err);
        }
        
        curl_close($curl);
        
        return $result;
    }
    
    public function post($url, $data = null)
    {
        $curl = $this->curlFactory($url, self::METHOD_POST, $data);
        $result = curl_exec($curl);
        $err    = curl_error($curl);
        
        if (!empty($err)) {
            throw new \RuntimeException('cURL Error: '. $err);
        }
        
        curl_close($curl);
        
        return $result;
    }
    
    protected function curlFactory($url, $method, $data = null)
    {
        $curl = curl_init();
        curl_setopt_array($curl, array(
            CURLOPT_USERAGENT       => $this->cfg('useragent', 'php/curl'),
            CURLOPT_FOLLOWLOCATION  => true,
            CURLOPT_URL             => $url,
            CURLOPT_TIMEOUT         => $this->cfg('timeout', 20),
            CURLOPT_RETURNTRANSFER  => true
        ));
        
        if ($method == self::METHOD_GET) {
            curl_setopt($curl, CURLOPT_HTTPGET, true);
        } elseif ($method == self::METHOD_POST) {
            curl_setopt($curl, CURLOPT_POST, true);
            
            curl_setopt($curl, CURLOPT_POSTFIELDS, $data);
            
            if (!is_array($data)) {
                curl_setopt($curl, CURLOPT_HTTPHEADER, array('Content-Type: text/plain'));
            }
        } else {
            curl_setopt($curl, CURLOPT_CUSTOMREQUEST, strtoupper($method));
        }
        
        $this->initCurlProxy($curl);
        $this->initCurlSSL($curl);
        
        return $curl;
    }
    
    private function initCurlProxy($curl)
    {
        if (null === $this->cfg('proxy.hostname', null)) {
            return;
        }
        
        curl_setopt(
            $curl, 
            CURLOPT_PROXY, 
            $this->cfg('proxy.hostname') .':'. $this->cfg('proxy.port')
        );
        
        curl_setopt($curl, CURLOPT_HTTPPROXYTUNNEL, true);
    }
    
    private function initCurlSSL($curl)
    {
        if (false === $this->cfg('ssl.verifypeer', true)) {
            curl_setopt_array($curl, array(
                CURLOPT_SSL_VERIFYPEER => false,
                CURLOPT_SSL_VERIFYHOST => 0
            ));
            
            return;
        }
        
        curl_setopt_array($curl, array(
            CURLOPT_SSL_VERIFYPEER => true,
            CURLOPT_SSL_VERIFYHOST => $this->cfg('ssl.verifyhost', 2)
        ));
        
        if (null !== $this->cfg('ssl.cainfo', null)) {
            curl_setopt($curl, CURLOPT_CAINFO, $this->cfg('ssl.cainfo'));
        }
    }
    
    protected function cfg($name, $default = false)
    {
        return (array_key_exists($name, $this->config) ? 
            $this->config[$name] : 
            $default
        );
    }
}