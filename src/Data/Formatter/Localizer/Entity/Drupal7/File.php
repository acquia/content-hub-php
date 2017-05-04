<?php

namespace Acquia\ContentHubClient\Data\Formatter\Localizer\Entity\Drupal7;

use Acquia\ContentHubClient\Attribute;

/**
 * File entity data localizer class.
 */
class File extends Entity
{
    /**
     * Localize "entity".
     *
     * @param mixed $data Data
     */
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

    /**
     * Localize "listEntities".
     *
     * @param mixed $data Data
     */
    public function localizeListEntities(&$data)
    {
        $this->transformer->rename($data['attributes'], 'filename', 'name');
        $this->transformer->multipleToSingle($data['attributes'], 'name');
    }

}
