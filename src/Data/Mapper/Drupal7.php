<?php

namespace Acquia\ContentHubClient\Data\Mapper;

use Acquia\ContentHubClient\Attribute;

class Drupal7 extends Mappable
{
    const VALID_UUID = '[0-9a-f]{8}-([0-9a-f]{4}-){3}[0-9a-f]{12}';

    private $defaultConfig = [
        'filters_mapping' => [
            'restricted_html' => 'filtered_html',
            'basic_html' => 'filtered_html',
            'full_html' => 'full_html',
            'rich_text' => 'full_html',
        ],
    ];

    public function __construct(array $config = []) {
        parent::__construct($config);
        $this->config += $this->defaultConfig;
    }

    protected function localizeEntity($data)
    {
        // language to langcode.
        // Only set language field if it is a node.
        if (isset($data['attributes']['langcode']) && $data['type'] == 'node') {
            $data['attributes']['language'] = $data['attributes']['langcode'];
            unset($data['attributes']['langcode']);
        }
        elseif (isset($data['attributes']['langcode']) && $data['type'] == 'taxonomy_term') {
            $data['attributes']['language'] = $data['attributes']['langcode'];
            unset($data['attributes']['langcode']);

            // Change vocabulary to type.
            $data['attributes']['type'] = $data['attributes']['vocabulary'];

            // Change name from array<string> to string.
            $data['attributes']['name']['type'] = Attribute::TYPE_STRING;
            foreach ($data['attributes']['name']['value'] as $language => $value) {
                $data['attributes']['name']['value'][$language] = reset($value);
            }

            // Change weight from array<string> to string.
            $data['attributes']['weight']['type'] = Attribute::TYPE_STRING;
            foreach ($data['attributes']['weight']['value'] as $language => $value) {
                $data['attributes']['weight']['value'][$language] = reset($value);
            }
        }
        elseif (isset($data['attributes']['langcode']) && $data['type'] == 'file') {
            // If it is a file coming from D8 then try to convert it to D7 format.
            $data['attributes']['language'] = $data['attributes']['langcode'];
            unset($data['attributes']['langcode']);

            // Now convert the filemime field to type field.
            $data['attributes']['type']['type'] = Attribute::TYPE_STRING;
            foreach ($data['attributes']['filemime']['value'] as $language => $value) {
                list($type, $format) = explode('/', reset($value));
                $data['attributes']['type']['value'][$language] = $type;
            }

            // Change filemime to mime.
            $data['attributes']['mime'] = $data['attributes']['filemime'];
            unset($data['attributes']['filemime']);
            $data['attributes']['mime']['type'] = Attribute::TYPE_STRING;
            foreach ($data['attributes']['mime']['value'] as $language => $value) {
                $data['attributes']['mime']['value'][$language] = reset($value);
            }

            // Change filename to name.
            $data['attributes']['name'] = $data['attributes']['filename'];
            unset($data['attributes']['filename']);
            $data['attributes']['name']['type'] = Attribute::TYPE_STRING;
            foreach ($data['attributes']['name']['value'] as $language => $value) {
                $data['attributes']['name']['value'][$language] = reset($value);
            }

            // Change filesize to size.
            $data['attributes']['size'] = $data['attributes']['filesize'];
            unset($data['attributes']['filesize']);
            $data['attributes']['size']['type'] = Attribute::TYPE_STRING;
            foreach ($data['attributes']['size']['value'] as $language => $value) {
                $data['attributes']['size']['value'][$language] = reset($value);
            }
        }

        foreach ($data['attributes'] as $attributeName => $attributeValue) {
            // Convert the input formats.
            if ($attributeValue['type'] === 'array<string>') {
                $data['attributes'][$attributeName]['type'] = 'string';
                foreach ($attributeValue['value'] as $langcode => $item) {

                    if ($this->isFileReference($data, reset($item))) {
                        $data['attributes'][$attributeName]['value'][$langcode] = reset($item);
                        continue;
                    }
                    $fieldValue = json_decode($item[0], TRUE);
                    if (!is_array($fieldValue) || !isset($fieldValue['format'])) {
                        $data['attributes'][$attributeName]['value'][$langcode] = reset($item);
                        continue;
                    }
                    // If default mapping is not satisfactory, assign
                    // 'filtered_html' by default.
                    $fieldValue['format'] = isset($this->config['filters_mapping'][$fieldValue['format']]) ? $this->config['filters_mapping'][$fieldValue['format']] : 'filtered_html';
                    $data['attributes'][$attributeName]['value'][$langcode] = json_encode($fieldValue, JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_AMP | JSON_HEX_QUOT);
                }
            }
        }

        return $data;
    }

    /**
     * Localizes the listEntities data.
     *
     * @param mixed $data
     *
     * @return mixed
     */
    protected function localizeListEntities($data)
    {
        return $data;
    }

    /**
     * Verifies that this is a valid UUID.
     *
     * @param string $uuid
     *
     * @return bool
     *   TRUE if this is a valid UUID, FALSE otherwise.
     */
    private function isUuid($uuid) {
        return (bool) preg_match('/^' . self::VALID_UUID . '$/', $uuid);
    }

    /**
     * Checks if the reference given is a file.
     *
     * @param array $entity
     *   Entity array.
     * @param string $value
     *   Value to check if it is a file.
     *
     * @return bool
     *   TRUE if it is a file, FALSE otherwise.
     */
    private function isFileReference($entity, $value) {
        preg_match('#\[(.*)\]#', $value, $match);
        $uuid = isset($match[1]) ? $match[1] : '';
        if (self::isUuid($uuid)) {
            foreach ($entity['assets'] as $asset) {
                if ($asset['replace-token'] === $value) {
                   return TRUE;
                }
            }
        }
        return FALSE;
    }
}
