<?php

namespace Acquia\ContentHubClient\Data\Formatter\Localizer\Entity;

class File
{
    public function localizeEntity($data)
    {
        // Change language to langcode.
        if (isset($data['attributes']['langcode'])) {
            $data['attributes']['language'] = $data['attributes']['langcode'];
            unset($data['attributes']['langcode']);
        }

        // Change type from array<string> to string.
        if (isset($data['attributes']['type'])) {
            $data['attributes']['type']['type'] = Attribute::TYPE_STRING;
            foreach ($data['attributes']['filemime']['value'] as $language => $value) {
                list($type, $format) = explode('/', reset($value));
                $data['attributes']['type']['value'][$language] = $type;
            }
        }

        // Change filemime to mime.
        if (isset($data['attributes']['filemime'])) {
            $data['attributes']['mime'] = $data['attributes']['filemime'];
            unset($data['attributes']['filemime']);
        }

        // Change mime from array<string> to string.
        if (isset($data['attributes']['mime'])) {
            $data['attributes']['mime']['type'] = Attribute::TYPE_STRING;
            foreach ($data['attributes']['mime']['value'] as $language => $value) {
                $data['attributes']['mime']['value'][$language] = reset($value);
            }
        }

        // Change filename to name.
        if (isset($data['attributes']['name'])) {
            $data['attributes']['name'] = $data['attributes']['filename'];
            unset($data['attributes']['filename']);
        }

        // Change name from array<string> to string.
        if (isset($data['attributes']['name'])) {
            $data['attributes']['name']['type'] = Attribute::TYPE_STRING;
            foreach ($data['attributes']['name']['value'] as $language => $value) {
                $data['attributes']['name']['value'][$language] = reset($value);
            }
        }

        // Change filesize to size.
        if (isset($data['attributes']['size'])) {
            $data['attributes']['size'] = $data['attributes']['filesize'];
            unset($data['attributes']['filesize']);
        }

        // Change size from array<string> to string.
        if (isset($data['attributes']['size'])) {
            $data['attributes']['size']['type'] = Attribute::TYPE_STRING;
            foreach ($data['attributes']['size']['value'] as $language => $value) {
                $data['attributes']['size']['value'][$language] = reset($value);
            }
        }

        return $data;
    }

}
