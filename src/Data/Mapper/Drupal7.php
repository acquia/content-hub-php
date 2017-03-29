<?php

namespace Acquia\ContentHubClient\Data\Mapper;

class Drupal7 extends Mappable
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
        // language to langcode.
        // Only set language field if it is a node.
        if (isset($data['attributes']['langcode']) && $data['type'] == 'node') {
            $data['attributes']['language'] = $data['attributes']['langcode'];
            unset($data['attributes']['langcode']);
        }

        foreach ($data['attributes'] as $attributeName => $attributeValue) {
            // Convert the input formats.
            if ($attributeValue['type'] === 'array<string>') {
                $data['attributes'][$attributeName]['type'] = 'string';
                foreach ($attributeValue['value'] as $langcode => $item) {
                    $fieldValue = json_decode($item[0], TRUE);
                    if (!is_array($fieldValue) || !isset($fieldValue['format'])) {
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

}
