<?php

namespace Acquia\ContentHubClient\Syndication\Queue;

final class SyndicationQueue {

  /**
   * The header required to specify when calling POST /queues/syndications
   */
  public const HEADER = 'X-Acquia-Content-Hub-Syndication';

  /**
   * The header value required to fetch items from the queue.
   */
  public const RECEIVE_QUEUE_ITEMS = 'ReceiveQueueItems';

}
