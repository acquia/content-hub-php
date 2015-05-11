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
     * Sets the 'uuid' parameter.
     *
     * @param string $uuid
     *
     * @return \Acquia\ContentServicesClient\Entity
     */
    public function setUuid($uuid)
    {
        $this['uuid'] = $uuid;

        return $this;
    }

    /**
     * Gets the 'uuid' parameter.
     *
     * @return string
     */
    public function getUuid()
    {
        return $this->getValue('uuid', '');
    }

    /**
     * Sets the 'type' parameter.
     *
     * @param string $type
     *
     * @return \Acquia\ContentServicesClient\Entity
     */
    public function setType($type)
    {
        $this['type'] = $type;

        return $this;
    }

    /**
     * Returns the 'type' parameter.
     *
     * @return string
     */
    public function getType()
    {
        return $this->getValue('type', '');
    }

    /**
     * Sets the 'created' parameter.
     *
     * @param string $created
     *
     * @return \Acquia\ContentServicesClient\Entity
     */
    public function setCreated($created)
    {
        $this['created'] = $created;

        return $this;
    }

    /**
     * Returns the 'created' parameter.
     *
     * @return string
     */
    public function getCreated()
    {
        return $this->getValue('created', '');
    }

    /**
     * Sets the 'modified' parameter.
     *
     * @param string $modified
     *
     * @return \Acquia\ContentServicesClient\Entity
     */
    public function setModified($modified)
    {
        $this['modified'] = $modified;

        return $this;
    }

    /**
     * Returns the 'modified' parameter.
     *
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
        $this['asset'] = $assets;

        return $this;
    }

    /**
     * Gets the assets associated with the Entity
     *
     * @return Asset[]
     */
    public function getAssets()
    {
        return $this->getValue('asset', []);
    }

    /**
     * Sets the attributes associated with the entity.
     *
     * @param Attribute[] $attributes
     *
     * @return \Acquia\ContentServicesClient\Entity
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
     *
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
        return $this->getValue('resource', '');
    }

    /**
     * Sets the origin.
     *
     * @param string $origin
     * @return \Acquia\ContentServicesClient\Entity
     */
    public function setOrigin($origin)
    {
        $this['origin'] = $origin;

        return $this;
    }

    /**
     * Returns the Origin's UUID.
     *
     * @return string
     */
    public function getOrigin()
    {
        return $this->getValue('origin', '');
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
