<?php

namespace Acquia\ContentHubClient\SearchCriteria;

use Acquia\ContentHubClient\CDF\CDFObject;

/**
 * Class SearchCriteria
 *
 * @package Drupal\acquia_contenthub\Client
 */
class Transformer {
  public static $supported_keys = [
      'search_term' => [
        'possible_keys' => ['search_term'],
        'default_value' => 'drupal8_content_entity',
      ],
      'type' => [
        'possible_keys' => ['type'],
        'default_value' => [],
      ],
      'bundle' => [
        'possible_keys' => ['bundle'],
        'default_value' => [],
      ],
      'tags' => [
        'possible_keys' => ['tags'],
        'default_value' => [],
      ],
      'label' => [
        'possible_keys' => ['label'],
        'default_value' => '',
      ],
      'start_date' => [
        'possible_keys' => ['start_date'],
        'default_value' => NULL,
      ],
      'end_date' => [
        'possible_keys' => ['end_date'],
        'default_value' => NULL,
      ],
      'from' => [
        'possible_keys' => ['from', 'start'],
        'default_value' => 0,
      ],
      'size' => [
        'possible_keys' => ['size', 'limit'],
        'default_value' => 1000,
      ],
      'sorting' => [
        'possible_keys' => ['sort'],
        'default_value' => '',
      ],
      'version' => [
        'possible_keys' => ['version'],
        'default_value' => '2.0',
      ],
      'languages' => [
        'possible_keys' => ['languages'],
        'default_value' => [CDFObject::LANGUAGE_UNDETERMINED],
      ],
    ];

  /**
   * @param array $data
   *
   * @return array
   */
  public static function arrayToSearchCriteriaArray(array $data): array {
    $result = [];

    foreach (self::$supported_keys as $key => $info) {
      foreach ($info['possible_keys'] as $nominee) {
        if (isset($data[$nominee])) {
          $result[$key] = is_array($info['default_value']) ? (array)$data[$nominee] : $data[$nominee];
          break;
        }
      }

      if (!isset($result[$key])) {
        $result[$key] = $info['default_value'];
      }
    }

    return $result;
  }

}
