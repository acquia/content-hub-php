<?php

namespace Acquia\ContentServicesClient;

class Asset extends \ArrayObject
{
    /**
     *
     * @param array $array
     */
    public function _construct(array $array = [])
    {
        parent::__construct($array);
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
        return $this->getValue('replaceToken', '');
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
