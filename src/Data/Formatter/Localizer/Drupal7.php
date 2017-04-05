<?php

namespace Acquia\ContentHubClient\Data\Formatter\Localizer;

class Drupal7 extends AbstractLocalizer
{
    protected function localizeEntity($data)
    {
        $this->localizeEntityByEntityType($data);

        foreach ($data['attributes'] as $attributeName => $attributeValue) {
            $this->localizeEntityByAttributeType($data['attributes'][$attributeName]);
        }

        return $data;
    }

    private function localizeEntityByEntityType(&$data) {
        $className = $this->getLocalizerClassName($data['type']);
        $localizerClassName = __NAMESPACE__ . '\\Entity\\Drupal7\\' . $className;
        if (!class_exists($localizerClassName)) {
            return;
        }

        $localizer = new $localizerClassName();
        $localizer->localizeEntity($data);
    }

    private function localizeEntityByAttributeType(&$data) {
        $className = $this->getLocalizerClassName($data['type']);
        $localizerClassName = __NAMESPACE__ . '\\Attribute\\Drupal7\\' . $className;
        if (!class_exists($localizerClassName)) {
            return;
        }

        $localizer = new $localizerClassName();
        $localizer->localizeEntity($data);
    }

    private function getLocalizerClassName($type) {
        return str_replace(['_', '<', '>'], '', ucwords($type, '_<>'));
    }

    /**
     * Localizes the listEntities data.
     *
     * @param mixed $data
     *
     * @return mixed
     */
    protected function localizeListEntities($data)
    {
        return $data;
    }
}
