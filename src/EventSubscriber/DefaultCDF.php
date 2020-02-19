<?php

namespace Acquia\ContentHubClient\EventSubscriber;

use Acquia\ContentHubClient\ContentHubLibraryEvents;
use Acquia\ContentHubClient\CDF\CDFObject;
use Acquia\ContentHubClient\Event\GetCDFTypeEvent;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

/**
 * The Configuration entity CDF creator.
 *
 * @see \Drupal\acquia_contenthub\Event\CreateCdfEntityEvent
 */
class DefaultCDF implements EventSubscriberInterface {

  /**
   * {@inheritdoc}
   */
  public static function getSubscribedEvents() {
    $events[ContentHubLibraryEvents::GET_CDF_CLASS][] = ['onGetCDFType'];
    return $events;
  }

  /**
   * Reacts on GET_CDF_CLASS event.
   *
   * @param \Acquia\ContentHubClient\Event\GetCDFTypeEvent $event
   *   Event.
   *
   * @throws \ReflectionException
   */
  public function onGetCDFType(GetCDFTypeEvent $event) { // phpcs:ignore
    $event->setObject(CDFObject::fromArray($event->getData()));
    $event->stopPropagation();
  }

}
