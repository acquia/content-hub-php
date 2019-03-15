<?php

namespace Acquia\ContentHubClient\EventSubscriber;

use Acquia\ContentHubClient\ContentHubLibraryEvents;
use Acquia\ContentHubClient\CDF\CDFObject;
use Acquia\ContentHubClient\Event\GetCDFTypeEvent;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

/**
 * The Configuration entity CDF creator.
 *
 * @see \Drupal\acquia_contenthub\Event\CreateCDFEntityEvent
 */
class DefaultCDF implements EventSubscriberInterface
{

  /**
   * {@inheritdoc}
   */
    public static function getSubscribedEvents()
    {
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
    public function onGetCDFType(GetCDFTypeEvent $event)
    {
        $data = $event->getData();

        $object = new CDFObject($data['type'], $data['uuid'], $data['created'], $data['modified'], $data['origin'], $data['metadata']);

        foreach ($data['attributes'] as $attribute_name => $values) {
            if (!$attribute = $object->getAttribute($attribute_name)) {
                $class = !empty($object->getMetadata()['attributes'][$attribute_name]) ? $object->getMetadata()['attributes'][$attribute_name]['class'] : false;
                if ($class && class_exists($class)) {
                    $object->addAttribute($attribute_name, $values['type'], null, CDFObject::LANGUAGE_UNDETERMINED, $class);
                } else {
                    $object->addAttribute($attribute_name, $values['type'], null);
                }
                $attribute = $object->getAttribute($attribute_name);
            }
            $value_property = (new \ReflectionClass($attribute))->getProperty('value');
            $value_property->setAccessible(true);
            $value_property->setValue($attribute, $values['value']);
        }
        $event->setObject($object);
        $event->stopPropagation();
    }
}
