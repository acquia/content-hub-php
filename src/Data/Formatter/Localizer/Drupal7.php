<?php

namespace Acquia\ContentHubClient\Data\Formatter\Localizer;

class Drupal7 extends AbstractLocalizer
{
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
            $entityLocalizer->localizeEntity($data);
        }

        foreach ($data['attributes'] as $attributeName => $attributeValue) {
            // Convert the input formats.
            if ($attributeValue['type'] === 'boolean') {
                foreach ($attributeValue['value'] as $langcode => $value) {
                    $data['attributes']['value'][$langcode] = $value === FALSE ? null : $value;
                }
            }
            if ($attributeValue['type'] === 'array<string>') {
                foreach ($attributeValue['value'] as $langcode => $items) {
                    foreach ($items as $key => $item) {
                        $fieldValue = json_decode($item, TRUE);
                        if (!is_array($fieldValue)) {
                            $items[$key] = $item;
                            continue;
                        }

                        // Localize Link fields - Hack.
                        // @TODO: Fix this for Links Type.
                        if (isset($fieldValue['uri'])) {
                            $fieldValue['url'] = $fieldValue['uri'];
                            unset($fieldValue['uri']);
                            $items[$key] = json_encode($fieldValue, JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_AMP | JSON_HEX_QUOT);
                            continue;
                        }

                        // Localize string fields.
                        if (!isset($fieldValue['format'])) {
                            $items[$key] = $item;
                            continue;
                        }
                        // If default mapping is not satisfactory, assign
                        // 'filtered_html' by default.
                        $fieldValue['format'] = isset($this->config['filters_mapping'][$fieldValue['format']]) ? $this->config['filters_mapping'][$fieldValue['format']] : 'filtered_html';
                        $items[$key] = json_encode($fieldValue, JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_AMP | JSON_HEX_QUOT);
                    }
                    $data['attributes'][$attributeName]['value'][$langcode] = $items;
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
}
