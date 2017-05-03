<?php

namespace Acquia\ContentHubClient\Data\Formatter\Localizer\Entity\Drupal7;

/**
 * Node entity data localizer class.
 */
class Node extends Entity {

  /**
   * Localize "listEntities".
   *
   * @param mixed $data Data
   */
  public function localizeListEntities(&$data)
  {
    if (!isset($data['attributes']['title'])) {
      return;
    }

    foreach ($data['attributes']['title'] as $language => $value) {
      $data['attributes']['title'][$language] = is_array($value) ? reset($value) : $value;
    }
  }
}
