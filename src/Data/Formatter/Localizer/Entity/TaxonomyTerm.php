<?php

namespace Acquia\ContentHubClient\Data\Formatter\Localizer\Entity;

class TaxonomyTerm
{
    public function localizeEntity($data)
    {
        // Change language to langcode.
        if (isset($data['attributes']['langcode'])) {
            $data['attributes']['language'] = $data['attributes']['langcode'];
            unset($data['attributes']['langcode']);
        }

        // Change vocabulary to type.
        if (isset($data['attributes']['vocabulary'])) {
            $data['attributes']['type'] = $data['attributes']['vocabulary'];
            unset($data['attributes']['vocabulary']);
        }

        // Change name from array<string> to string.
        if (isset($data['attributes']['name'])) {
            $data['attributes']['name']['type'] = Attribute::TYPE_STRING;
            foreach ($data['attributes']['name']['value'] as $language => $value) {
                $data['attributes']['name']['value'][$language] = reset($value);
            }
        }

        // Change weight from array<string> to string.
        if (isset($data['attributes']['weight'])) {
            $data['attributes']['weight']['type'] = Attribute::TYPE_STRING;
            foreach ($data['attributes']['weight']['value'] as $language => $value) {
                $data['attributes']['weight']['value'][$language] = reset($value);
            }
        }

        return $data;
    }

}
