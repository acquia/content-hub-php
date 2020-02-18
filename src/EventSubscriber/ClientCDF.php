<?php

namespace Acquia\ContentHubClient\EventSubscriber;

use Acquia\ContentHubClient\ContentHubLibraryEvents;
use Acquia\ContentHubClient\CDF\ClientCDFObject;
use Acquia\ContentHubClient\Event\GetCDFTypeEvent;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

/**
 * The Configuration entity CDF creator.
 *
 * @see \Drupal\acquia_contenthub\Event\CreateCdfEntityEvent
 */
class ClientCDF implements EventSubscriberInterface {

  /**
   * {@inheritdoc}
   */
  public static function getSubscribedEvents() {
    $events[ContentHubLibraryEvents::GET_CDF_CLASS][] = ['onGetCDFType', 100];

    return $events;
  }

  /**
   * Reacts on GET_CDF_CLASS event.
   *
   * @param \Acquia\ContentHubClient\Event\GetCDFTypeEvent $event
   *   Event.
   *
   * @throws \Exception
   */
  public function onGetCDFType(GetCDFTypeEvent $event) {  // phpcs:ignore
    if ($event->getType() === 'client') {
      $data = $event->getData();
      /* @deprecated Backwards Compatibility, Remove by 2.0 */
      if (!isset($data['metadata']['settings'])) {
        $data['metadata'] = [
          'settings' => $data['metadata'],
        ];
      }
      /* End deprecated code */
      $object = ClientCDFObject::fromArray($data);
      $event->setObject($object);
      $event->stopPropagation();
    }
  }

}
