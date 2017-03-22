<?php

namespace Acquia\ContentHubClient\Data\Mapper;

class Drupal7 extends Mappable
{
    private $to_langcode = 'und';

    protected function localizeEntity($data, $config)
    {
        $from_lancode = 'en';

        // language to langcode.
        $data['attributes']['language'] = $data['attributes']['langcode'];
        $data['attributes']['language']['value'][$from_lancode] = $this->to_langcode;
        unset($data['attributes']['langcode']);

        foreach ($data['attributes'] as $attribute_name => $attribute_value) {
            $data['attributes'][$attribute_name]['value'][$this->to_langcode] = $data['attributes'][$attribute_name]['value'][$from_lancode];
            unset($data['attributes'][$attribute_name]['value'][$from_lancode]);

            if ($attribute_value['type'] == 'array<string>') {
                foreach ($data['attributes'][$attribute_name]['value'][$this->to_langcode] as $key => $item) {
                    $field_value = json_decode($item, TRUE);
                    if (is_array($field_value) && isset($field_value['format'])) {

                        switch ($field_value['format']) {
                            case 'basic html':
                                $field_value['format'] = 'filtered_html';
                                break;

                            case 'rich_text':
                                $field_value['format'] = 'full_html';
                                break;

                        }

                        $data['attributes'][$attribute_name]['value'][$this->to_langcode][$key] = json_encode($field_value, JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_AMP | JSON_HEX_QUOT);
                    }
                }
            }
        }

        return $data;
    }

}
