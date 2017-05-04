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

    /**
     * Localize "listEntities".
     *
     * @param mixed $data Data
     */
    public function localizeListEntities(&$data)
    {
        $this->transformer->multipleToSingle($data['attributes'], 'name');
    }

}
