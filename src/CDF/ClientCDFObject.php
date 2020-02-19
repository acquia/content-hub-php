<?php

namespace Acquia\ContentHubClient\CDF;

use Acquia\ContentHubClient\CDFAttribute;
use Acquia\ContentHubClient\Settings;

/**
 * Class ClientCDFObject.
 *
 * @package Acquia\ContentHubClient\CDF
 */
class ClientCDFObject extends CDFObject {

  /**
   * Settings object.
   *
   * @var \Acquia\ContentHubClient\Settings
   */
  protected $settings;

  /**
   * ClientCDFObject constructor wrapper.
   *
   * @param string $uuid
   *   Object UUID.
   * @param array $metadata
   *   Object metadata.
   *
   * @return \Acquia\ContentHubClient\CDF\ClientCDFObject
   *   The ClientCDFObject instance.
   *
   * @throws \Exception
   */
  public static function create($uuid, array $metadata) {
    $cdf = new static('client', $uuid, date('c'), date('c'), $uuid, $metadata);
    $cdf->addAttribute('clientname', CDFAttribute::TYPE_STRING, $metadata['settings']['name']);
    return $cdf;
  }

  /**
   * Grabs the clientname on the cdf.
   *
   * @return \Acquia\ContentHubClient\CDFAttribute
   *   The 'clientname' attribute.
   */
  public function getClientName() {
    return $this->getAttribute('clientname');
  }

  /**
   * Grabs the settings object instead of the attributes which are an array.
   *
   * @return \Acquia\ContentHubClient\Settings
   *   Settings object.
   */
  public function getSettings() {
    // Add all the client settings as attributes to the client object.
    if (empty($this->settings)) {
      $metadata = $this->getMetadata();
      $this->settings = new Settings(
        $metadata['settings']['name'],
        $metadata['settings']['uuid'],
        $metadata['settings']['apiKey'],
        $metadata['settings']['secretKey'],
        $metadata['settings']['url'],
        $metadata['settings']['sharedSecret'],
        $metadata['settings']['webhook']
      );
    }
    return $this->settings;
  }

  /**
   * Grabs the webhook for the client.
   *
   * @return array
   *   Webhook array.
   */
  public function getWebhook() {
    $metadata = $this->getMetadata();
    if (isset($metadata['settings']['webhook'])) {
      return $metadata['settings']['webhook'];
    }

    return [];
  }

}
