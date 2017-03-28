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

    protected function localizeEntity($data, $config)
    {
        // This has to be given as an argument: filter mapping D8 => D7.
        $config += $this->defaultConfig;

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
                    $fieldValue['format'] = isset($config['filters_mapping'][$fieldValue['format']]) ? $config['filters_mapping'][$fieldValue['format']] : 'filtered_html';
                    $data['attributes'][$attributeName]['value'][$langcode] = json_encode($fieldValue, JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_AMP | JSON_HEX_QUOT);
                }
            }
        }

        return $data;
    }

    /**
     * Localizes the listEntities method.
     *
     * @param $data
     */
    protected function localizeListEntities($data)
    {
        if (empty($data['data'])) {
            return $data;
        }

        // Language Code.
        $fromLancode = 'en';
        foreach($data['data'] as $key => $item) {
            foreach ($item['attributes'] as $attributeName => $attributeValue) {
                if (isset($attributeValue[$fromLancode])) {
                    unset($item['attributes'][$attributeName][$fromLancode]);
                }
            }
            $data['data'][$key] = $item;
        }

        return $data;
    }

}
