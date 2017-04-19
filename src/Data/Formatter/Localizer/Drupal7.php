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
     * Localizes List Entities by Entity Type.
     *
     * @param mixed $data
     *
     * @return mixed
     */
    private function localizeListEntitiesByEntityType(&$data) {
        if ($data['type'] == 'taxonomy_term') {
            foreach ($data['attributes']['name'] as $language => $value) {
                $data['attributes']['name'][$language] = is_array($value) ? reset($value) : $value;
            }
        }
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
        foreach ($data['data'] as $key => $item) {
            $this->localizeListEntitiesByEntityType($item);
            $data['data'][$key] = $item;
        }
        return $data;
    }
}
