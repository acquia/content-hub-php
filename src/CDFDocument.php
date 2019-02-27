<?php

namespace Acquia\ContentHubClient;

use Acquia\ContentHubClient\CDF\CDFObject;

class CDFDocument {

  /**
   * @var \Acquia\ContentHubClient\CDF\CDFObject[]
   */
  protected $entities;

  public function __construct(CDFObject ...$entities) {
    $this->setCDFEntities(...$entities);
  }

  public function getEntities() {
    return $this->entities;
  }

  public function getCDFEntity($uuid) {
    return $this->entities[$uuid] ?? null;
  }

  public function setCDFEntities(CDFObject ...$entities) {
    //unset($this->entities);
    //With unset() if $entities list is empty, hasEntities() will give an error
    $entitiesList = [];
    foreach ($entities as $entity) {
        $entitiesList[$entity->getUuid()] = $entity;
    }
    $this->entities = $entitiesList;
  }

  public function addCDFEntity(CDFObject $object) {
    $this->entities[$object->getUuid()] = $object;
  }

  public function removeCDFEntity($uuid) {
    unset($this->entities[$uuid]);
  }

  public function hasEntities() {
    return (bool) $this->entities;
  }

  public function hasEntity($uuid) {
    return !empty($this->entities[$uuid]);
  }

  public function mergeDocuments(CDFDocument $document) {
    foreach ($document->getEntities() as $entity) {
      $this->addCDFEntity($entity);
    }
  }

  public function toString() {
    $entities = [];
    foreach ($this->getEntities() as $entity) {
      $entities[] = $entity->toArray();
    }
    $output = ['entities' => $entities];
    return json_encode($output, JSON_PRETTY_PRINT);
  }

}
