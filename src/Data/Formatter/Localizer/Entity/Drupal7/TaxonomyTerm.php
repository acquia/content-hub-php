<?php

namespace Acquia\ContentHubClient\Data\Formatter\Localizer\Entity\Drupal7;

class TaxonomyTerm extends Entity
{
    public function localizeEntity(&$data)
    {
        parent::localizeEntity($data);

        $this->massager->rename($data['attributes'], 'vocabulary', 'type');
        $this->massager->arrayStringToString($data['attributes'], 'name');
        $this->massager->arrayStringToString($data['attributes'], 'weight');

        return $data;
    }

}
