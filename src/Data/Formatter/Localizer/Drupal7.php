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
        if (!isset($data['type'])) {
            return;
        }

        $localizerClassName = $this->getLocalizerClassName($data['type'], 'Entity');
        if (!class_exists($localizerClassName)) {
            // If you're here and still need additional localization, you should
            // now create the entity-specific class. See $localizerClassName for
            // its anticipated path.
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
        if (!isset($data['type'])) {
            return;
        }

        $localizerClassName = $this->getLocalizerClassName($data['type'], 'Attribute');
        if (!class_exists($localizerClassName)) {
            // If you're here and still need additional localization, you should
            // now create the attribute-specific class. See $localizerClassName
            // for its anticipated path.
            return;
        }

        $localizer = new $localizerClassName();
        $localizer->localizeEntity($data);
    }

    /**
     * Localize "listEntities".
     *
     * @param mixed $data
     */
    protected function localizeListEntities(&$data)
    {
        if (!isset($data['data'])) {
            return;
        }

        foreach ($data['data'] as $key => $item) {
            $this->localizeListEntitiesByEntityType($data['data'][$key]);
        }
    }

    /**
     * Localize "listEntities" by entity type.
     *
     * @param mixed $data Data
     */
    private function localizeListEntitiesByEntityType(&$data)
    {
        if (!isset($data['type'])) {
            return;
        }

        $localizerClassName = $this->getLocalizerClassName($data['type'], 'Entity');
        if (!class_exists($localizerClassName)) {
            // If you're here and still need additional localization, you should
            // now create the entity-specific class. See $localizerClassName for
            // its anticipated path.
            return;
        }

        $localizer = new $localizerClassName();
        $localizer->localizeListEntities($data);
    }

}
