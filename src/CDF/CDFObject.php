<?php

namespace Acquia\ContentHubClient\CDF;

use Acquia\ContentHubClient\CDFAttribute;

class CDFObject implements CDFObjectInterface {

  const LANGUAGE_UNDETERMINED = 'und';

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
   * @var bool
   */
  protected $processed = FALSE;

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
    $this->setMetadata($metadata);
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

  public function getModuleDependencies() {
    return !empty($this->metadata['dependencies']['module']) ? $this->metadata['dependencies']['module'] : [];
  }

  public function getDependencies() {
    return !empty($this->metadata['dependencies']['entity']) ? $this->metadata['dependencies']['entity'] : [];
  }

  public function hasProcessedDependencies() {
    return $this->processed;
  }

  public function markProcessedDependencies() {
    $this->processed = TRUE;
  }

  public function getAttributes() {
    return $this->attributes;
  }

  /**
   * @param $id
   *
   * @return \Acquia\ContentHubClient\CDFAttribute
   */
  public function getAttribute($id) {
    if (!empty($this->attributes[$id])) {
      return $this->attributes[$id];
    }
  }

  /**
   * Factory could be used here in order to stick with Single responsibility principle.
   *
   * @param string $id
   * @param string $type Type of attribute
   * @param null $value Attribute value
   * @param string $language
   * @param string $className Class name of the created object
   * @throws \Exception
   */
  public function addAttribute($id, $type, $value = NULL, $language = self::LANGUAGE_UNDETERMINED, $className = CDFAttribute::class) {
    if ($className !== CDFAttribute::class && !is_subclass_of($className, CDFAttribute::class)) {
      throw new \Exception(sprintf("The %s class must be a subclass of \Acquia\ContentHubClient\CDFAttribute", $className));
    }
    $attribute = new $className($id, $type, $value, $language);
    $this->attributes[$attribute->getId()] = $attribute;
    // Keep track of the class used for this attribute.
    if ($className !== CDFAttribute::class) {
      $this->metadata['attributes'][$attribute->getId()]['class'] = $className;
    } else {
      unset($this->metadata['attributes'][$attribute->getId()]);
    }
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
