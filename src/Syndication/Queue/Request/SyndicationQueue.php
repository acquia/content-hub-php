<?php

namespace Acquia\ContentHubClient\Syndication\Queue\Request;

/**
 * Provides the necessary details to fulfill the api contract.
 */
final class SyndicationQueue {

  /**
   * The header required to specify when calling POST /queues/syndications.
   */
  public const HEADER = 'X-Acquia-Content-Hub-Syndication';

  /**
   * The header value required to fetch items from the queue.
   */
  public const RECEIVE_QUEUE_ITEMS = 'ReceiveQueueItems';

  /**
   * The header value required to confirm processed queue items.
   */
  public const CONFIRM_PROCESSED_QUEUE_ITEMS = 'ConfirmProcessedQueueItems';

  /**
   * The header value required to create queue item.
   */
  public const CREATE_QUEUE_ITEMS = 'CreateQueueItems';

}
