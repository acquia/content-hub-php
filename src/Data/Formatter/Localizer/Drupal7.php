<?php

namespace Acquia\ContentHubClient\Data\Formatter\Localizer;

/**
 * Drupal 7 data localizer class.
 */
class Drupal7 extends AbstractLocalizer
{
    /**
     * Localize "listEntities".
     *
     * @param mixed $data Data
     */
    protected function localizeEntity(&$data)
    {
        $this->localizeEntityByEntityType($data);

        foreach ($data['attributes'] as $attributeName => $attributeValue) {
            $this->localizeEntityByAttributeType($data['attributes'][$attributeName]);
        }
    }

    /**
     * Localize "Entity" by entity type.
     *
     * @param mixed $data Data
     */
    private function localizeEntityByEntityType(&$data) {
        $localizerClassName = $this->getLocalizerClassName($data['type'], 'Entity');
        if (!class_exists($localizerClassName)) {
            return;
        }

        $localizer = new $localizerClassName();
        $localizer->localizeEntity($data);
    }

    /**
     * Localize "Entity" by attribute type.
     *
     * @param mixed $data Data
     */
    private function localizeEntityByAttributeType(&$data) {
        $localizerClassName = $this->getLocalizerClassName($data['type'], 'Attribute');
        if (!class_exists($localizerClassName)) {
            return;
        }

        $localizer = new $localizerClassName();
        $localizer->localizeEntity($data);
    }

    /**
     * Localize "listEntities".
     *
     * @param mixed $data
     *
     * @return mixed
     */
    protected function localizeListEntities($data)
    {
        if (!isset($data['data'])) {
            return;
        }

        foreach ($data['data'] as $key => $item) {
            $this->localizeListEntitiesByEntityType($item);
            $data['data'][$key] = $item;
        }
        return $data;
    }

    /**
     * Localize "listEntities" by entity type.
     *
     * @param mixed $data Data
     */
    private function localizeListEntitiesByEntityType(&$data)
    {
        $localizerClassName = $this->getLocalizerClassName($data['type'], 'Entity');
        if (!class_exists($localizerClassName)) {
            return;
        }

        $localizer = new $localizerClassName();
        $localizer->localizeListEntities($data);
    }

}
