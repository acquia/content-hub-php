<?php

namespace Acquia\ContentHubClient;

use Symfony\Component\Serializer\Serializer;
use Symfony\Component\Serializer\Encoder\JsonEncoder;
use Symfony\Component\Serializer\Normalizer\CustomNormalizer;

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
     * @return \Acquia\ContentHubClient\Entity
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
     * @return \Acquia\ContentHubClient\Entity
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
     * @return \Acquia\ContentHubClient\Entity
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
     * @return \Acquia\ContentHubClient\Entity
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
     * @param Metadata[] $metadata
     *
     * @return \Acquia\ContentHubClient\Entity
     */
    public function setMetaData($metadata)
    {
        $this['metadata'] = $metadata;

        return $this;
    }

    /**
     * Gets the metadata associated with the Entity
     *
     * @return Metadata[]
     */
    public function getMetadata()
    {
        return $this->getValue('metadata', []);
    }

    /**
     * @param Asset[] $assets
     *
     * @return \Acquia\ContentHubClient\Entity
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
     * Adds an Asset to the Entity.
     *
     * @param Asset $asset
     * @return $this
     */
    public function addAsset(Asset $asset)
    {
        $assets = $this->getAssets();
        foreach ($assets as $myasset) {
            if ($myasset->getReplaceToken() == $asset->getReplaceToken()) {
                // Make sure not to add an asset with the same token.
                return $this;
            }
        }
        $this['assets'][] = $asset;
        return $this;
    }

    /**
     * Returns an Asset, given a replaceToken.
     *
     * @param $replaceToken
     * @return Asset|bool
     */
    public function getAsset($replaceToken)
    {
        $assets = $this->getAssets();
        foreach ($assets as $asset) {
            if ($asset->getReplaceToken() == $replaceToken) {
                return $asset;
            }
        }
        return FALSE;
    }

    /**
     * Removes an Asset from the Entity.
     *
     * @param $replaceToken
     * @return $this
     */
    public function removeAsset($replaceToken) {
        $assets = $this->getAssets();
        foreach ($assets as $key => $asset) {
            if ($asset->getReplaceToken() == $replaceToken) {
                unset($assets[$key]);
                $this->setAssets($assets);
                continue;
            }
        }
        return $this;
    }

    /**
     * Sets the attributes associated with the entity.
     *
     * @param Attribute[] $attributes
     *
     * @return \Acquia\ContentHubClient\Entity
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
     * Sets an Attribute.
     *
     * Only accepts the attribute if it has values, otherwise it is ignored.
     *
     * @param string    $name
     * @param Attribute $attribute
     *
     * @return \Acquia\ContentHubClient\Entity
     */
    public function setAttribute($name, Attribute $attribute)
    {
        if ($attribute->hasValues()) {
            $this['attributes'][$name] = $attribute;
        }

        return $this;
    }

    /**
     * Returns an Attribute, given the name.
     *
     * @param $name
     * @return \Acquia\ContentHubClient\Attribute|bool
     */
    public function getAttribute($name)
    {
      return isset($this['attributes'][$name]) ? $this['attributes'][$name] : FALSE;
    }

    /**
     * Removes an Attribute from the array.
     *
     * @param $name
     *
     * @return \Acquia\ContentHubClient\Entity
     */
    public function removeAttribute($name)
    {
        $attributes = $this->getAttributes();
        unset($attributes[$name]);
        $this->setAttributes($attributes);
        return $this;
    }

    /**
     * Sets the Value for an Attribute in a particular language.
     *
     * @param $name
     * @param $value
     * @param string $lang
     *
     * @return $this
     */
    public function setAttributeValue($name, $value, $lang = Attribute::LANGUAGE_DEFAULT) {
        if ($this->getAttribute($name)) {
            $this['attributes'][$name]->setValue($value, $lang);
        }
        return $this;
    }

    /**
     * Sets the origin.
     *
     * @param string $origin
     * @return \Acquia\ContentHubClient\Entity
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
     * Returns the json representation of the current object.
     *
     * @return string
     */
    public function json()
    {
        $encoders = array(new JsonEncoder());
        $normalizers = array(new CustomNormalizer());
        $serializer = new Serializer($normalizers, $encoders);
        return $serializer->serialize($this, 'json');
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
