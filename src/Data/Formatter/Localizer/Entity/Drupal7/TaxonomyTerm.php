<?php

namespace Acquia\ContentHubClient\Data\Formatter\Localizer\Entity\Drupal7;

class TaxonomyTerm extends Entity
{
    public function localizeEntity(&$data)
    {
        parent::localizeEntity($data);

        $this->transformer->rename($data['attributes'], 'vocabulary', 'type');
        $this->transformer->arrayStringToString($data['attributes'], 'name');
        $this->transformer->arrayStringToString($data['attributes'], 'weight');
    }

}
