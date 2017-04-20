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
        $localizerClassName = $this->getLocalizerClassName($data['type'], 'Entity');
        if (!class_exists($localizerClassName)) {
            return;
        }

        $localizer = new $localizerClassName();
        $localizer->localizeEntity($data);
    }

    private function localizeEntityByAttributeType(&$data) {
        $localizerClassName = $this->getLocalizerClassName($data['type'], 'Attribute');
        if (!class_exists($localizerClassName)) {
            return;
        }

        $localizer = new $localizerClassName();
        $localizer->localizeEntity($data);
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
        if (!isset($data['data'])) {
            return;
        }

        foreach ($data['data'] as $key => $item) {
            $this->localizeListEntitiesByEntityType($item);
            $data['data'][$key] = $item;
        }
        return $data;
    }

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
