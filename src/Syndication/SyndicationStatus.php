<?php

namespace Acquia\ContentHubClient\Syndication;

/**
 * Represents possible syndication statuses.
 *
 * @package Acquia\ContentHubClient\Syndication
 */
final class SyndicationStatus {

  public const UNKNOWN = 'UNKNOWN';
  public const READY_FOR_SYNDICATION = 'READY-FOR-SYNDICATION';
  public const SYNDICATION_IN_PROGRESS = 'SYNDICATION-IN-PROGRESS';
  public const SYNDICATION_FAILED = 'SYNDICATION-FAILED';
  public const SYNDICATION_SUCCESSFUL = 'SYNDICATION-SUCCESSFUL';
  public const QUEUED_TO_EXPORT = 'QUEUED-TO-EXPORT';
  public const QUEUED_TO_IMPORT = 'QUEUED-TO-IMPORT';
  public const EXPORT_SUCCESSFUL = 'EXPORT-SUCCESSFUL';
  public const EXPORT_FAILED = 'EXPORT-FAILED';
  public const IMPORT_SUCCESSFUL = 'IMPORT-SUCCESSFUL';
  public const IMPORT_FAILED = 'IMPORT-FAILED';

}
