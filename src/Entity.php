<?php

namespace Acquia\ContentServicesClient;

class Entity extends \ArrayObject
{

    /**
     * @param array $array
     */
    public function __construct(array $array = [])
    {
        parent::__construct($array);
    }

    /**
     * @param string $uuid
     *
     * @return \Acquia\ContentServicesApi\Entity
     */
    public function setUuid($uuid)
    {
        $this['uuid'] = $uuid;

        return $this;
    }

    /**
     *
     * @return string
     */
    public function getUuid()
    {
        return $this->getValue('uuid', '');
    }

    /**
     * @param string $type
     *
     * @return \Acquia\ContentServicesApi\Entity
     */
    public function setType($type)
    {
        $this['type'] = $type;

        return $this;
    }

    /**
     *
     * @return string
     */
    public function getType()
    {
        return $this->getValue('type', '');
    }

    /**
     * @param string $created
     *
     * @return \Acquia\ContentServicesApi\Entity
     */
    public function setCreated($created)
    {
        $this['created'] = $created;

        return $this;
    }

    /**
     *
     * @return string
     */
    public function getCreated()
    {
        return $this->getValue('created', '');
    }

    /**
     * @param string $modified
     *
     * @return \Acquia\ContentServicesApi\Entity
     */
    public function setModified($modified)
    {
        $this['modified'] = $modified;

        return $this;
    }

    /**
     * @return string
     */
    public function getModified()
    {
        return $this->getValue('modified', '');
    }

    /**
     * @param Asset[] $assets
     *
     * @return \Acquia\ContentServicesClient\Entity
     */
    public function setAssets($assets)
    {
        $this['assets'] = $assets;

        return $this;
    }

    /**
     * Gets the assets associated with the Entity
     *
     * @return Asset[]
     */
    public function getAssets()
    {
        return $this->getValue('assets', []);
    }

    /**
     *
     * @param Attribute[] $attributes
     */
    public function setAttributes($attributes)
    {
        $this['attributes'] = $attributes;

        return $this;
    }

    /**
     * Gets the attributes associated with the Entity
     *
     * @return Attribute[]
     */
    public function getAttributes()
    {
        return $this->getValue('attributes', []);
    }

    /**
     *
     * @param type $url
     * @return \Acquia\ContentServicesClient\Entity
     */
    public function setResource($url)
    {
        $this['resource'] = $url;

        return $this;
    }

    /**
     *
     * @return type
     */
    public function getResource()
    {
        return $this['resource'];
    }

    /**
     *
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
