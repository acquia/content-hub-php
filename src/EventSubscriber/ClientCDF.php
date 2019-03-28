<?php

namespace Acquia\ContentHubClient\EventSubscriber;

use Acquia\ContentHubClient\ContentHubLibraryEvents;
use Acquia\ContentHubClient\CDF\ClientCDFObject;
use Acquia\ContentHubClient\Event\GetCDFTypeEvent;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

/**
 * The Configuration entity CDF creator.
 *
 * @see \Drupal\acquia_contenthub\Event\CreateCDFEntityEvent
 */
class ClientCDF implements EventSubscriberInterface
{

    /**
     * {@inheritdoc}
     */
    public static function getSubscribedEvents()
    {
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
    public function onGetCDFType(GetCDFTypeEvent $event)
    {
        if ($event->getType() === 'client') {
            $data = $event->getData();
            // Backwards compatibility
            if (!isset($data['metadata']['settings'])) {
              $settings = $data['metadata'];
              $data['metadata']['settings'] = $settings;
            }
            if (!isset($data['metadata']['extradata'])) {
              $data['metadata']['extradata'] = [];
            }
            $object = new ClientCDFObject($data['uuid'], $data['metadata']['settings'], $data['metadata']['extradata']);
            $event->setObject($object);
            $event->stopPropagation();
        }
    }
}
