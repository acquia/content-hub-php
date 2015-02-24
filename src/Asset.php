<?php

namespace Acquia\ContentServicesClient;

class Asset extends \ArrayObject
{
    /**
     * @param string $url
     * @param string $replaceToken
     */
    public function _construct($url, $replaceToken)
    {
        $this['url'] = $url;
        $this['replaceToken'] = $replaceToken;
    }
    
    /**
     * @param string $url
     * 
     * @return \Acquia\ContentServicesClient\Asset
     */
    public function setUrl($url)
    {
        $this['url'] = $url;
        return $this;
    }
    
    /**
     * @param string $replaceToken
     * 
     * @return \Acquia\ContentServicesClient\Asset
     */
    public function setReplaceToken($replaceToken)
    {
        $this['replaceToken'] = $replaceToken;
        return $this;
    }
    
    /**
     * 
     * @return string
     */
    public function getUrl()
    {
        return $this->getValue('url', '');
    }
    
    /**
     * 
     * @return string
     */
    public function getReplaceToken()
    {
        return $this->getValue('replceToken', '');
    }
    
    /**
     * @param string $key
     * @param string $default
     * 
     * @return mixed
     */
    protected function getValue($key, $default)
    {
        return isset($this[$key]) ? $this[$key] : $default;
    }
}
