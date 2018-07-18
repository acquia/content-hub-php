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
   * Constructs a Settings object.
   *
   * @param string $name
   * @param string $uuid
   * @param string $api_key
   * @param string $secret_key
   * @param string $url
   * @param null|string $shared_secret
   */
    public function __construct($name, $uuid, $api_key, $secret_key, $url, $shared_secret = NULL)
    {
        $this->name = $name;
        $this->uuid = $uuid;
        $this->apiKey = $api_key;
        $this->secretKey = $secret_key;
        $this->url = $url;
        $this->sharedSecret = $shared_secret;
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

    public function getApiKey() {
      return $this->apiKey;
    }

    /**
     * Returns the Shared Secret used for Webhook verification.
     *
     * @return string|bool
     *   The shared secret if it is set, FALSE otherwise.
     */
    public function getSecretKey()
    {
        return $this->secretKey;
    }

    public function getUrl() {
        return $this->url;
    }

    public function getMiddleware()
    {
        $key = new Key($this->getApiKey(), $this->getSecretKey());
        return new HmacAuthMiddleware($key);
    }

    /**
     * @return null|string
     */
    public function getSharedSecret() {
        return $this->sharedSecret;
    }

}
