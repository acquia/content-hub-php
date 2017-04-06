<?php

namespace Acquia\ContentHubClient\Data\Formatter\Localizer\Attribute\Drupal7;

class ArrayString
{
    private $config = [
        'filters_mapping' => [
            'restricted_html' => 'filtered_html',
            'basic_html' => 'filtered_html',
            'full_html' => 'full_html',
            'rich_text' => 'full_html',
        ],
    ];

    public function localizeEntity(&$data)
    {
        foreach ($data['value'] as $langcode => $valueList) {
            foreach ($valueList as $key => $value) {
                $fieldValue = json_decode($value, TRUE);
                if (!is_array($fieldValue)) {
                    $valueList[$key] = $value;
                    continue;
                }

                // Address fields - Hack.
                if (isset($fieldValue['country_code'])) {
                    $fieldValue['country'] = $fieldValue['country_code'];
                    $fieldValue['thoroughfare'] = $fieldValue['address_line1'];
                    $fieldValue['premise'] = $fieldValue['address_line2'];
                    $fieldValue['organisation_name'] = $fieldValue['organization'];
                    $fieldValue['first_name'] = $fieldValue['given_name'];
                    $fieldValue['last_name'] = $fieldValue['family_name'];
                    $to_unset = array('country_code', 'address_line1', 'address_line2', 'organization', 'given_name', 'family_name');
                    $fieldValue = array_diff_key($fieldValue, array_flip($to_unset));
                    $valueList[$key] = json_encode($fieldValue, JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_AMP | JSON_HEX_QUOT);
                    continue;
                }

                // Localize Link fields - Hack.
                // @TODO: Fix this for Links Type.
                if (isset($fieldValue['uri'])) {
                    // Checking if it is an internal URL. If it is,
                    // then strip out the 'internal:/' prefix.
                    $fieldValue['url'] = (substr($fieldValue['uri'], 0, 10) === 'internal:/') ? str_replace('internal:/', '', $fieldValue['uri']) : $fieldValue['uri'];
                    unset($fieldValue['uri']);
                    $valueList[$key] = json_encode($fieldValue, JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_AMP | JSON_HEX_QUOT);
                    continue;
                }

                // Localize string fields.
                if (!isset($fieldValue['format'])) {
                    $valueList[$key] = $value;
                    continue;
                }
                // If default mapping is not satisfactory, assign
                // 'filtered_html' by default.
                $fieldValue['format'] = isset($this->config['filters_mapping'][$fieldValue['format']]) ? $this->config['filters_mapping'][$fieldValue['format']] : 'filtered_html';
                $valueList[$key] = json_encode($fieldValue, JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_AMP | JSON_HEX_QUOT);
            }
            $data['value'][$langcode] = $valueList;
        }
    }

}
