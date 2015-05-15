<?php
/**
 * @file
 * Handles the User data.
 */

namespace Acquia\ContentServicesClient;


class User extends \ArrayObject
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
     * Helper method to get the value of a property.
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

    /**
     * Returns the UserId.
     *
     * @return string
     */
    public function getUuid()
    {
        return $this->getValue('uuid', '');
    }

    /**
     * Returns the 'Created' property.
     *
     * @return string
     */
    public function getCreated()
    {
        return $this->getValue('created', '');
    }

    /**
     * Returns the 'Modified' property.
     *
     * @return mixed
     */
    public function getModified()
    {
        return $this->getValue('modified', '');
    }

    /**
     * Returns an array of Webhooks registered for this particular user.
     *
     * @return array
     */
    public function getWebhooks()
    {
        return $this->getValue('webhooks', []);
    }

} 