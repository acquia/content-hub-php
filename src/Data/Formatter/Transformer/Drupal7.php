<?php

namespace Acquia\ContentHubClient\Data\Formatter\Transformer;

use Acquia\ContentHubClient\Attribute;

class Drupal7 extends General
{
    public function arrayStringToString(&$data, $index)
    {
        if (!isset($data[$index])) {
            return;
        }

        $data[$index]['type'] = Attribute::TYPE_STRING;
        foreach ($data[$index]['value'] as $languageId => $value) {
            $data[$index]['value'][$languageId] = reset($value);
        }
    }

}
