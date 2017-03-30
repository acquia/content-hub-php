<?php

namespace Acquia\ContentHubClient\Data\Formatter\Localizer\Entity\Drupal7;

use Acquia\ContentHubClient\Attribute;

class File extends Entity
{
    public function localizeEntity(&$data)
    {
        parent::localizeEntity($data);

        // Change type from array<string> to string.
        if (!isset($data['attributes']['type'])) {
            $data['attributes']['type']['type'] = Attribute::TYPE_STRING;
            foreach ($data['attributes']['filemime']['value'] as $language => $value) {
                list($type, $format) = explode('/', reset($value));
                $data['attributes']['type']['value'][$language] = $type;
            }
        }

        $this->transformer->rename($data['attributes'], 'filemime', 'mime');
        $this->transformer->arrayStringToString($data['attributes'], 'mime');

        $this->transformer->rename($data['attributes'], 'filename', 'name');
        $this->transformer->arrayStringToString($data['attributes'], 'name');

        $this->transformer->rename($data['attributes'], 'filesize', 'size');
        $this->transformer->arrayStringToString($data['attributes'], 'size');
    }

}
