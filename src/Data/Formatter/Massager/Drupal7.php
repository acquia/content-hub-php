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
        if (isset($data['attributes'][$index])) {
            $data['attributes'][$index]['type'] = Attribute::TYPE_STRING;
            foreach ($data['attributes'][$index]['value'] as $languageId => $value) {
                $data['attributes'][$index]['value'][$languageId] = reset($value);
            }
        }
    }
}
