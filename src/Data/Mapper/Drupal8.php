<?php

namespace Acquia\ContentHubClient\Data\Mapper;

class Drupal8 extends Mappable
{
    protected function standardizeEntity($data, $config)
    {
        return $data;
    }

}
