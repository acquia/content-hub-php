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

        foreach ($data['attributes'] as $attribute_name => $attribute_value) {
            // Convert the input formats.
            if ($attribute_value['type'] === 'array<string>') {
                $data['attributes'][$attribute_name]['type'] = 'string';
                foreach ($attribute_value['value'] as $langcode => $item) {
                    $field_value = json_decode($item[0], TRUE);
                    if (!is_array($field_value) || !isset($field_value['format'])) {
                        continue;
                    }
                    // If default mapping is not satisfactory, assign
                    // 'filtered_html' by default.
                    $field_value['format'] = isset($config['filters_mapping'][$field_value['format']]) ? $config['filters_mapping'][$field_value['format']] : 'filtered_html';
                    $data['attributes'][$attribute_name]['value'][$langcode] = json_encode($field_value, JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_AMP | JSON_HEX_QUOT);
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
        $from_lancode = 'en';
        foreach($data['data'] as $key => $item) {
            foreach ($item['attributes'] as $attribute_name => $attribute_value) {
                if (isset($attribute_value[$from_lancode])) {
                    unset($item['attributes'][$attribute_name][$from_lancode]);
                }
            }
            $data['data'][$key] = $item;
        }

        return $data;
    }

}
