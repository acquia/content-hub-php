<?php

namespace Acquia\ContentHubClient;

class CDFObject {

  /**
   * @var string
   */
  protected $type;

  /**
   * @var string
   */
  protected $uuid;

  /**
   * @var string
   */
  protected $created;

  /**
   * @var string
   */
  protected $modified;

  /**
   * @var string
   */
  protected $origin;

  /**
   * @var array
   */
  protected $metadata = [];

  /**
   * @var CDFAttribute[]
   */
  protected $attributes = [];

  /**
   * CDFObject constructor.
   *
   * @param string $type
   * @param string $uuid
   * @param string $created
   * @param string $modified
   * @param string $origin
   * @param array $metadata
   */
  public function __construct($type, $uuid, $created, $modified, $origin, $metadata = []) {
    $this->type = $type;
    $this->uuid = $uuid;
    $this->created = $created;
    $this->modified = $modified;
    $this->origin = $origin;
    $this->metadata = $metadata;
  }

  /**
   * @return string
   */
  public function getType() {
    return $this->type;
  }

  /**
   * @return string
   */
  public function getUuid() {
    return $this->uuid;
  }

  /**
   * @return string
   */
  public function getCreated() {
    return $this->created;
  }

  /**
   * @return string
   */
  public function getModified() {
    return $this->modified;
  }

  /**
   * @return string
   */
  public function getOrigin() {
    return $this->origin;
  }

  /**
   * @return array
   */
  public function getMetadata() {
    return $this->metadata;
  }

  public function setMetadata(array $metadata) {
    $this->metadata = $metadata;
  }

  public function getAttributes() {
    return $this->attributes;
  }

  public function getAttribute($id) {
    if (!empty($this->attributes[$id])) {
      return $this->attributes[$id];
    }
  }

  public function addAttribute(CDFAttribute $attribute) {
    $this->attributes[$attribute->getId()] = $attribute;
  }

  public function toArray() {
    $output = [
      'uuid' => $this->getUuid(),
      'type' => $this->getType(),
      'created' => $this->getCreated(),
      'modified' => $this->getModified(),
      'origin' => $this->getOrigin(),
    ];
    if ($attributes = $this->getAttributes()) {
      foreach ($attributes as $attribute) {
        $output['attributes'][$attribute->getId()] = $attribute->toArray();
      }
    }
    if ($metadata = $this->getMetadata()) {
      $output['metadata'] = $metadata;
    }
    return $output;
  }

}