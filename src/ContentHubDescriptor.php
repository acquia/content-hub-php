<?php

namespace Acquia\ContentHubClient;

use function GuzzleHttp\default_user_agent;

/**
 * Content Hub Descriptor provides Version and user agent string.
 */
final class ContentHubDescriptor {

  /**
   * Library version for client.
   */
  public const LIB_VERSION = '3.2';

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
    return self::LIBRARYNAME . '/' . self::LIB_VERSION . ' ' . default_user_agent();
  }

}
