<?php

namespace Acquia\ContentHubClient;

/**
 * Class Asset.
 *
 * @package Acquia\ContentHubClient
 */
class Asset extends \ArrayObject {

  /**
   * URL setter.
   *
   * @param string $url
   *   URL.
   */
  public function setUrl(string $url): void {
    $this['url'] = $url;
  }

  /**
   * Setter for replace token.
   *
   * @param string $replaceToken
   *   Token value.
   */
  public function setReplaceToken(string $replaceToken): void {
    $this['replace-token'] = $replaceToken;
  }

  /**
   * URL getter.
   *
   * @return string
   *   URL property.
   */
  public function getUrl(): string {
    return $this->getValue('url', '');
  }

  /**
   * Getter for 'replace-token'.
   *
   * @return string
   *   'replace-token' value.
   */
  public function getReplaceToken(): string {
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
    return $this[$key] ?? $default;
  }

}
