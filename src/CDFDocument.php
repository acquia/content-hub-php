<?php

namespace Acquia\ContentHubClient;

use Acquia\ContentHubClient\CDF\CDFObject;

/**
 * Class CDFDocument.
 *
 * @package Acquia\ContentHubClient
 */
class CDFDocument {

  /**
   * Entities list.
   *
   * @var \Acquia\ContentHubClient\CDF\CDFObject[]
   */
  protected $entities;

  /**
   * CDFDocument constructor.
   *
   * @param \Acquia\ContentHubClient\CDF\CDFObject[] $entities
   *   Entities list.
   */
  public function __construct(CDFObject ...$entities) { // phpcs:ignore
    $this->setCdfEntities(...$entities);
  }

  /**
   * Returns entities list.
   *
   * @return \Acquia\ContentHubClient\CDF\CDFObject[]
   *   Entities list.
   */
  public function getEntities() {
    return $this->entities;
  }

  /**
   * Returns entity by UUID.
   *
   * @param string $uuid
   *   Entity UUID.
   *
   * @return \Acquia\ContentHubClient\CDF\CDFObject|null
   *   CDFObject if exists, otherwise NULL
   */
  public function getCdfEntity($uuid) {
    return $this->entities[$uuid] ?? NULL;
  }

  /**
   * Entities setter.
   *
   * @param \Acquia\ContentHubClient\CDF\CDFObject[] $entities
   *   Entities list.
   */
  public function setCdfEntities(CDFObject ...$entities) { // phpcs:ignore
    $entitiesList = [];
    foreach ($entities as $entity) {
      $entitiesList[$entity->getUuid()] = $entity;
    }
    $this->entities = $entitiesList;
  }

  /**
   * Appends CDF object to document.
   *
   * @param \Acquia\ContentHubClient\CDF\CDFObject $object
   *   CDF object.
   */
  public function addCdfEntity(CDFObject $object) {
    $this->entities[$object->getUuid()] = $object;
  }

  /**
   * Removes entity from document.
   *
   * @param string $uuid
   *   Entity UUID.
   */
  public function removeCdfEntity($uuid) {
    unset($this->entities[$uuid]);
  }

  /**
   * Checks if document contains entities.
   *
   * @return bool
   *   TRUE if entities exists, otherwise FALSE.
   */
  public function hasEntities() {
    return (bool) $this->entities;
  }

  /**
   * Checks that entity exists in document.
   *
   * @param string $uuid
   *   Entity UUID.
   *
   * @return bool
   *   TRUE if entity exists, otherwise FALSE.
   */
  public function hasEntity($uuid) {
    return !empty($this->entities[$uuid]);
  }

  /**
   * Merges CDF document into current.
   *
   * @param \Acquia\ContentHubClient\CDFDocument $document
   *   CDF document.
   */
  public function mergeDocuments(CDFDocument $document) {
    foreach ($document->getEntities() as $entity) {
      $this->addCdfEntity($entity);
    }
  }

  /**
   * Converts CDF document to string (JSON).
   *
   * @return false|string
   *   String representation.
   */
  public function toString() {
    $entities = [];
    foreach ($this->getEntities() as $entity) {
      $entities[] = $entity->toArray();
    }
    $output = ['entities' => $entities];

    return json_encode($output, JSON_PRETTY_PRINT);
  }

}
