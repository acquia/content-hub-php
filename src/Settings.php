<?php

namespace Acquia\ContentHubClient;

use Acquia\Hmac\Guzzle\HmacAuthMiddleware;
use Acquia\Hmac\Key;

/**
 * Settings for the current subscription.
 *
 * Class Settings
 * @package Acquia\ContentHubClient
 */
class Settings
{

    /**
     * The name of this settings group.
     *
     * @var string
     */
    protected $name;

    /**
     * The assigned UUID from ContentHub.
     *
     * @var string
     */
    protected $uuid;

    /**
     * The api key of these settings.
     *
     * @var string
     */
    protected $apiKey;

    /**
     * The shared secret.
     *
     * @var string
     */
    protected $sharedSecret;

    /**
     * The group of webhooks associated with these settings.
     *
     * @var array
     */
    protected $webhooks = [];

    /**
     * The group of clients associated with these settings.
     *
     * @var array
     */
    protected $clients = [];

    /**
     * The URL of the end point to consult.
     *
     * @var string
     */
    protected $url;

  /**
     * Constructs a Settings object.
     *
     * @param $name
     * @param $uuid
     * @param $api_key
     * @param $shared_secret
     */
    public function __construct($name, $uuid, $api_key, $shared_secret, $url)
    {
        $this->name = $name;
        $this->uuid = $uuid;
        $this->apiKey = $api_key;
        $this->sharedSecret = $shared_secret;
        $this->url = $url;
    }

    /**
     * Get the settings name.
     *
     * @return string
     */
    public function getName() {
        return $this->name;
    }

    /**
     * Returns the Uuid.
     *
     * @return string
     */
    public function getUuid()
    {
        return $this->uuid;
    }

    /**
     * Returns an array of Webhooks registered for this particular subscription.
     *
     * @return array
     */
    public function getWebhooks()
    {
        return $this->webhooks;
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
        return $this->clients;
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

    public function getApiKey() {
      return $this->apiKey;
    }

    /**
     * Returns the Shared Secret used for Webhook verification.
     *
     * @return string|bool
     *   The shared secret if it is set, FALSE otherwise.
     */
    public function getSharedSecret()
    {
        return $this->sharedSecret;
    }

    public function getUrl() {
        return $this->url;
    }

    public function getMiddleware()
    {
        $key = new Key($this->getApiKey(), $this->getSharedSecret());
        return new HmacAuthMiddleware($key);
    }

}
