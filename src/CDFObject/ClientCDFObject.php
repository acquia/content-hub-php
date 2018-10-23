<?php

namespace Acquia\ContentHubClient\CDFObject;

use Acquia\ContentHubClient\Attribute\CDFDataAttribute;
use Acquia\ContentHubClient\CDFObject;
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
   * @param \Acquia\ContentHubClient\Settings $settings
   */
  public function __construct(string $uuid, Settings $settings) {
    parent::__construct('client', $uuid, date('c'), date('c'), $uuid);

    // Add all the client settings as attributes to the client object.
    $this->addAttribute('clientname', CDFDataAttribute::TYPE_STRING, $this->settings->getName());
    $this->addAttribute('settings', CDFDataAttribute::TYPE_ARRAY_KEYWORD, $settings->toArray());
    $this->addAttribute('webhook', CDFDataAttribute::TYPE_ARRAY_KEYWORD, $settings->toArray()['webhook']);
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
   * @return \Acquia\ContentHubClient\CDFAttribute
   */
  public function getWebhook() {
    return $this->getAttribute('webhook');
  }
}
