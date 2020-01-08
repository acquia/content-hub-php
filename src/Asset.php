<?php

namespace Acquia\ContentHubClient;

/**
 * Class Asset.
 *
 * @package Acquia\ContentHubClient
 */
class Asset extends \ArrayObject {

  /**
   * Asset constructor.
   *
   * @param array $array
   *   Asset data.
   */
  public function __construct(array $array = []) {
    parent::__construct($array);
  }

  /**
   * URL setter.
   *
   * @param string $url
   *   URL.
   *
   * @return \Acquia\ContentHubClient\Asset
   *   Asset with URL.
   */
  public function setUrl($url) {
    $this['url'] = $url;

    return $this;
  }

  /**
   * Setter for replace token.
   *
   * @param string $replaceToken
   *   Token value.
   *
   * @return \Acquia\ContentHubClient\Asset
   *   Asset.
   */
  public function setReplaceToken($replaceToken) {
    $this['replace-token'] = $replaceToken;

    return $this;
  }

  /**
   * URL getter.
   *
   * @return string
   *   URL property.
   */
  public function getUrl() {
    return $this->getValue('url', '');
  }

  /**
   * Getter for 'replace-token'.
   *
   * @return string
   *   'replace-token' value.
   */
  public function getReplaceToken() {
    return $this->getValue('replace-token', '');
  }

  /**
   * Properties getter.
   *
   * @param string $key
   *   Property name.
   * @param string $default
   *   Default value.
   *
   * @return mixed
   *   Property value.
   */
  protected function getValue($key, $default) {
    return isset($this[$key]) ? $this[$key] : $default;
  }

}
