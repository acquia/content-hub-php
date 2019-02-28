<?php

namespace Acquia\ContentHubClient\CDF;

use Acquia\ContentHubClient\CDFAttribute;
use Acquia\ContentHubClient\Settings;

class ClientCDFObject extends CDFObject {

  /**
   * @var Settings
   */
  protected $settings;

  /**
   * ClientCDFObject constructor.
   *
   * @param string $uuid
   * @param array $settings
   * @throws \Exception
   */
  public function __construct($uuid, array $settings) {
    parent::__construct('client', $uuid, date('c'), date('c'), $uuid);

    // Add all the client settings as attributes to the client object.
    $this->settings = new Settings($settings['name'], $settings['uuid'], $settings['apiKey'], $settings['secretKey'], $settings['url'], $settings['sharedSecret'], $settings['webhook']);
    $this->setMetadata($settings);
    $this->addAttribute('clientname', CDFAttribute::TYPE_STRING, $this->settings->getName());
  }

  /**
   * Grabs the clientname on the cdf.
   *
   * @return \Acquia\ContentHubClient\CDFAttribute
   */
  public function getClientName() {
    return $this->getAttribute('clientname');
  }

  /**
   * Grabs the settings object instead of the attributes which are an array.
   *
   * @return \Acquia\ContentHubClient\Settings
   */
  public function getSettings() {
    return $this->settings;
  }

  /**
   * Grabs the webhook for the client.
   *
   * @return array
   */
  public function getWebhook() {
    $metadata = $this->getMetadata();
    if (isset($metadata['webhook'])) {
      return $metadata['webhook'];
    }
    return [];
  }
}
