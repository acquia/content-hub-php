<?php

namespace Acquia\ContentHubClient\Data\Formatter\Localizer;

use Acquia\ContentHubClient\Attribute;

class Drupal7 extends Localizable
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
        $typeLocalizerClassName = str_replace('_', '', ucwords($data['type'], '_'));
        $entityTypeLocalizerClassName = __NAMESPACE__ . '\\Entity\\Drupal7\\' . $typeLocalizerClassName;
        if (class_exists($entityTypeLocalizerClassName)) {
            $entityLocalizer = new $entityTypeLocalizerClassName();
            $data = $entityLocalizer->localizeEntity($data);
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
        if ($this->isUuid($uuid)) {
            foreach ($entity['assets'] as $asset) {
                if ($asset['replace-token'] === $value) {
                   return TRUE;
                }
            }
        }
        return FALSE;
    }
}
