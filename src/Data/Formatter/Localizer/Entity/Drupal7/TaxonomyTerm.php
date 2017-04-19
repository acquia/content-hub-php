<?php

namespace Acquia\ContentHubClient\Data\Formatter\Localizer\Entity\Drupal7;

/**
 * Taxonomy term entity data localizer class.
 */
class TaxonomyTerm extends Entity
{
    /**
     * Localize "entity".
     *
     * @param mixed $data Data
     */
    public function localizeEntity(&$data)
    {
        parent::localizeEntity($data);

        $this->transformer->duplicate($data['attributes'], 'vocabulary', 'type');
        $this->transformer->arrayStringToString($data['attributes'], 'name');
        $this->transformer->arrayStringToString($data['attributes'], 'weight');
        $this->transformer->arrayStringToString($data['attributes'], 'description');
        $this->transformer->addArrayReferenceIfNotExist($data['attributes'], 'parent');
    }

    public function localizeListEntities(&$data)
    {
        if (!isset($data['attributes']['name'])) {
            return;
        }

        foreach ($data['attributes']['name'] as $language => $value) {
            $data['attributes']['name'][$language] = is_array($value) ? reset($value) : $value;
        }
    }

}
