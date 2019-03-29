<?php

namespace Acquia\ContentHubClient\CDF;

use Acquia\ContentHubClient\CDFAttribute;
use Acquia\ContentHubClient\Settings;

/**
 * Class ClientCDFObject.
 *
 * @package Acquia\ContentHubClient\CDF
 */
class ClientCDFObject extends CDFObject
{

    /**
     * @var Settings
     */
    protected $settings;

    /**
     * ClientCDFObject constructor.
     *
     * @param string $uuid
     * @param array $settings
     *
     * @throws \Exception
     */
    public function __construct($type, $uuid, $created, $modified, $origin, $metadata = [])
    {
        parent::__construct($type, $uuid, $created, $modified, $origin, $metadata);
        $this->addAttribute('clientname', CDFAttribute::TYPE_STRING, $metadata['settings']['name']);
    }

    public static function create($uuid, $metadata) {
      return new static('client', $uuid, date('c'), date('c'), $uuid, $metadata);
    }

    /**
     * Grabs the clientname on the cdf.
     *
     * @return \Acquia\ContentHubClient\CDFAttribute
     */
    public function getClientName()
    {
        return $this->getAttribute('clientname');
    }

    /**
     * Grabs the settings object instead of the attributes which are an array.
     *
     * @return \Acquia\ContentHubClient\Settings
     */
    public function getSettings()
    {
      if (empty($this->settings)) {
        $this->settings = new Settings($this->metadata['settings']['name'], $this->metadata['settings']['uuid'], $this->metadata['settings']['apiKey'], $this->metadata['settings']['secretKey'], $this->metadata['settings']['url'], $this->metadata['settings']['sharedSecret'], $this->metadata['settings']['webhook']);
      }
      return $this->settings;
    }

    /**
     * Grabs the webhook for the client.
     *
     * @return array
     */
    public function getWebhook()
    {
        $metadata = $this->getMetadata();
        if (isset($metadata['settings']['webhook'])) {
            return $metadata['settings']['webhook'];
        }

        return [];
    }
}
