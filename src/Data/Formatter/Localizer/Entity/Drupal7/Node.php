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
      $this->transformer->multipleToSingle($data['attributes'], 'title');
  }
}
