<?php

namespace Acquia\ContentHubClient\Syndication;

/**
 * Represents possible logging error/info events.
 *
 * @package Acquia\ContentHubClient\Syndication
 */
final class SyndicationEvents {

  public const SEVERITY_ERROR = 'ERROR';

  public const SEVERITY_INFO = 'INFO';

  public const EXPORT_FAILURE = [
    'name' => 'Entity Export failure',
    'severity' => self::SEVERITY_ERROR,
  ];

  public const IMPORT_FAILURE = [
    'name' => 'Entity Import failure',
    'severity' => self::SEVERITY_ERROR,
  ];

  public const IMPORT_SUCCESS = [
    'name' => 'Entity Import success',
    'severity' => self::SEVERITY_INFO
  ];

}
