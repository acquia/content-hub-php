<?php

namespace Acquia\ContentHubClient\Data\Formatter\Transformer\Entity;

use Acquia\ContentHubClient\Attribute;
use Acquia\ContentHubClient\Data\Formatter\Transformer\General;

/**
 * Drupal 7 data transformer class.
 */
class Drupal7 extends General
{
    /**
     * Transform - array string to string.
     *
     * @param mixed $data Data to transform
     * @param mixed $index Transform data of index
     */
    public function arrayStringToString(&$data, $index)
    {
        if (!isset($data[$index]) || empty($data[$index]['type']) || empty($data[$index]['value'])) {
            return;
        }

        $data[$index]['type'] = Attribute::TYPE_STRING;
        foreach ($data[$index]['value'] as $languageId => $value) {
            $data[$index]['value'][$languageId] = reset($value);
        }
    }

    /**
     * Transform - add array reference if it doesn't exist already.
     *
     * @param mixed $data Data to transform
     * @param mixed $index Transform data of index
     */
    public function addArrayReferenceIfNotExist(&$data, $index) {
        if (isset($data[$index])) {
            return;
        }

        $data[$index]['type'] = Attribute::TYPE_ARRAY_REFERENCE;

        // @TODO: Should we add this to all languages for this entity?
        // It is not required, but seems we are doing that in the standarized
        // format.
        $data[$index]['value'][Attribute::LANGUAGE_DEFAULT] = [];
    }

}
