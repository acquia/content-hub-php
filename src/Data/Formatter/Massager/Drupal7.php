<?php

namespace Acquia\ContentHubClient\Data\Formatter\Massager;

use Acquia\ContentHubClient\Attribute;

class Drupal7
{
    public function rename(&$data, $fromIndex, $toIndex)
    {
        if (!isset($data[$fromIndex])) {
            return;
        }

        $data[$toIndex] = $data[$fromIndex];
        unset($data[$fromIndex]);
    }

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
