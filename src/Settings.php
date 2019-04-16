<?php

namespace Acquia\ContentHubClient;

use Acquia\Hmac\Guzzle\HmacAuthMiddleware;
use Acquia\Hmac\Key;

/**
 * Class Settings
 *
 * Settings for the current subscription.
 *
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
    protected $secretKey;

    /**
     * The URL of the end point to consult.
     *
     * @var string
     */
    protected $url;

    /**
     * The shared secret.
     *
     * @var string|null
     */
    protected $sharedSecret;

    /**
     * The webhook UUID & URL associated with the client.
     *
     * @var array|null
     */
    protected $webhook;

    /**
     * Constructs a Settings object.
     *
     * @param string $name
     *   Name of this settings group.
     * @param string $uuid
     *   The assigned UUID from ContentHub.
     * @param string $api_key
     *   The assigned UUID from ContentHub.
     * @param string $secret_key
     *   Secret key.
     * @param string $url
     *   The URL of the end point to consult.
     * @param null|string $shared_secret
     *   Shared secret.
     * @param array $webhook
     *   Webhook UUID & URL associated with the client.
     */
    public function __construct($name, $uuid, $api_key, $secret_key, $url, $shared_secret = null, $webhook = [])
    {
        $this->name = $name;
        $this->uuid = $uuid;
        $this->apiKey = $api_key;
        $this->secretKey = $secret_key;
        $this->url = $url;
        $this->sharedSecret = $shared_secret;
        $this->webhook = $webhook;
    }

    /**
     * Transforms settings object to array.
     *
     * @return array
     *   Array representation.
     */
    public function toArray()
    {
        return [
          'name' => $this->name,
          'uuid' => $this->uuid,
          'apiKey' => $this->apiKey,
          'secretKey' => $this->secretKey,
          'url' => $this->url,
          'sharedSecret' => $this->sharedSecret,
          'webhook' => $this->webhook,
        ];
    }

    /**
     * Get the settings name.
     *
     * @return string
     *   Name attribute.
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * Returns the Uuid.
     *
     * @return string | boolean
     *   UUID attribute.
     */
    public function getUuid()
    {
        return !empty($this->uuid) ? $this->uuid : false;
    }

    /**
     * Returns the webhook UUID associated with this site.
     *
     * @param string $op
     *   Optional setting to grab the specific key for a webhook. Options are:
     *     * uuid: The UUID for the webhook.
     *     * url: The fully qualified URL that plexus accesses the site.
     *     * settings_url: The nice URL that users interact with.
     *
     * @return string
     *    The Webhook if set from connection settings.
     */
    public function getWebhook($op = 'settings_url')
    {
        if (empty($this->webhook) || !isset($this->webhook[$op])) {
            return false;
        }

        return $this->webhook[$op];
    }

    /**
     * Returns URL of the endpoint.
     *
     * @return string
     *   URL of the endpoint.
     */
    public function getUrl()
    {
        return $this->url;
    }

    /**
     * Returns middleware.
     *
     * @return \Acquia\Hmac\Guzzle\HmacAuthMiddleware
     *   Auth middleware.
     */
    public function getMiddleware()
    {
        $key = new Key($this->getApiKey(), $this->getSecretKey());

        return new HmacAuthMiddleware($key);
    }

    /**
     * Returns API key of these settings.
     *
     * @return string
     *   API key of these settings.
     */
    public function getApiKey()
    {
        return $this->apiKey;
    }

    /**
     * Returns the API Secret Key used for Webhook verification.
     *
     * @return string|bool
     *   The api secret key if it is set, FALSE otherwise.
     */
    public function getSecretKey()
    {
        return $this->secretKey;
    }

    /**
     * @return null|string
     */
    public function getSharedSecret()
    {
        return $this->sharedSecret;
    }
}
