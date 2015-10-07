<?php
/**
 * @file
 * Handles the User data.
 */

namespace Acquia\ContentHubClient;

/**
 * Settings for the current subscription.
 *
 * Class Settings
 * @package Acquia\ContentHubClient
 */
class Settings extends \ArrayObject
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
     * Returns an array of Webhooks registered for this particular subscription.
     *
     * @return array
     */
    public function getWebhooks()
    {
        return $this->getValue('webhooks', []);
    }

    /**
     * Returns a Webhook, given a URL.
     *
     * @param $webhook_url
     *
     * @return array|bool
     */
    public function getWebhook($webhook_url)
    {
        $webhooks = $this->getWebhooks();
        foreach ($webhooks as $webhook) {
            if ($webhook['url'] == $webhook_url) {
                return $webhook;
            }
        }
        return FALSE;
    }

    /**
     * Returns an array of Clients for this particular subscription.
     *
     * @return array
     */
    public function getClients()
    {
        return $this->getValue('clients', []);
    }

    /**
     * Gets a Client, given a name.
     *
     * @param $name
     *   The client name.
     * @return array|bool
     */
    public function getClient($name)
    {
        $clients = $this->getClients();
        foreach ($clients as $client) {
            if ($client['name'] == $name) {
                return $client;
            }
        }
        return FALSE;
    }

    /**
     * Returns the Shared Secret used for Webhook verification.
     *
     * @return string|bool
     *   The shared secret if it is set, FALSE otherwise.
     */
    public function getSharedSecret()
    {
        return $this->getValue('shared_secret', FALSE);
    }

    /**
     * Returns the 'success' parameter.
     *
     * @return bool
     */
    public function success()
    {
        return (bool) $this->getValue('success', 0);
    }

} 