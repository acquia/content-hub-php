<?php

namespace Acquia\ContentHubClient;

class Asset extends \ArrayObject
{
    /**
     *
     * @param array $array
     */
    public function __construct(array $array = [])
    {
        parent::__construct($array);
    }

    /**
     * @param string $url
     *
     * @return \Acquia\ContentHubClient\Asset
     */
    public function setUrl($url)
    {
        $this['url'] = $url;

        return $this;
    }

    /**
     * @param string $replaceToken
     *
     * @return \Acquia\ContentHubClient\Asset
     */
    public function setReplaceToken($replaceToken)
    {
        $this['replace-token'] = $replaceToken;

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
        return $this->getValue('replace-token', '');
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
