<?php

namespace Acquia\ContentHubClient\Data\Formatter\Localizer\Entity\Drupal7;

use Acquia\ContentHubClient\Attribute;

class File extends Entity
{
    public function localizeEntity(&$data)
    {
        parent::localizeEntity($data);

        // Change type from array<string> to string.
        if (isset($data['attributes']['type'])) {
            $data['attributes']['type']['type'] = Attribute::TYPE_STRING;
            foreach ($data['attributes']['filemime']['value'] as $language => $value) {
                list($type, $format) = explode('/', reset($value));
                $data['attributes']['type']['value'][$language] = $type;
            }
        }

        $this->massager->rename($data['attributes'], 'filemime', 'mime');
        $this->massager->arrayStringToString($data['attributes'], 'mime');

        $this->massager->rename($data['attributes'], 'filename', 'name');
        $this->massager->arrayStringToString($data['attributes'], 'name');

        $this->massager->rename($data['attributes'], 'filesize', 'size');
        $this->massager->arrayStringToString($data['attributes'], 'size');

        return $data;
    }

}
