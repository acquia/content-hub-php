<?php

namespace Acquia\ContentHubClient;

use GuzzleHttp\Utils;

/**
 * Content Hub Descriptor provides Version and user agent string.
 */
final class ContentHubDescriptor {

  /**
   * Library version for client.
   */
  public const LIB_VERSION = '3.6.x-dev';

  /**
   * Library name for client.
   */
  public const LIBRARYNAME = 'AcquiaContentHubPHPLib';

  /**
   * Returns default user agent string.
   *
   * @return string
   *   User agent string.
   */
  public static function userAgent(): string {
    // GuzzleHttp\default_user_agent() was removed in Guzzle 7; use Utils::defaultUserAgent() instead.
    $guzzleAgent = class_exists('GuzzleHttp\Utils')
      ? Utils::defaultUserAgent()
      : \GuzzleHttp\default_user_agent();
    return self::LIBRARYNAME . '/' . self::LIB_VERSION . ' ' . $guzzleAgent;
  }

}
