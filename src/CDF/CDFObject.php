<?php

namespace Acquia\ContentHubClient\CDF;

use Acquia\ContentHubClient\CDFAttribute;

/**
 * Class CDFObject.
 *
 * @package Acquia\ContentHubClient\CDF
 */
class CDFObject implements CDFObjectInterface {

  const LANGUAGE_UNDETERMINED = 'und';

  /**
   * Object type.
   *
   * @var string
   */
  protected $type;

  /**
   * Object UUID.
   *
   * @var string
   */
  protected $uuid;

  /**
   * Created date.
   *
   * @var string
   */
  protected $created;

  /**
   * Modified date.
   *
   * @var string
   */
  protected $modified;

  /**
   * Origin UUID.
   *
   * @var string
   */
  protected $origin;

  /**
   * Object metadata.
   *
   * @var array
   */
  protected $metadata = [];

  /**
   * Attributes list.
   *
   * @var \Acquia\ContentHubClient\CDFAttribute[]
   */
  protected $attributes = [];

  /**
   * Indicates that object have processed dependencies.
   *
   * @var bool
   */
  protected $processed = FALSE;

  /**
   * CDFObject constructor.
   *
   * @param string $type
   *   Object type.
   * @param string $uuid
   *   Object UUID.
   * @param string $created
   *   Created date.
   * @param string $modified
   *   Modified date.
   * @param string $origin
   *   Origin UUID.
   * @param array $metadata
   *   Object metadata.
   */
  public function __construct(
    $type,
    $uuid,
    $created,
    $modified,
    $origin,
    array $metadata = []
  ) {
    $this->type = $type;
    $this->uuid = $uuid;
    $this->created = $created;
    $this->modified = $modified;
    $this->origin = $origin;
    $this->setMetadata($metadata);
  }

  /**
   * Static Factory method to allow CDFObject to interpret their own data.
   *
   * @param array $data
   *   Initial data.
   *
   * @return \Acquia\ContentHubClient\CDF\CDFObject
   *   CDFObject.
   *
   * @throws \ReflectionException
   */
  public static function fromArray(array $data) {
    $object = new static($data['type'], $data['uuid'], $data['created'], $data['modified'], $data['origin'], $data['metadata']);
    foreach ($data['attributes'] as $attribute_name => $values) {
      if (!$attribute = $object->getAttribute($attribute_name)) {
        $class = $object->getMetadata()['attributes'][$attribute_name]['class'] ?? 'non-existing-class';

        if (class_exists($class)) {
          $object->addAttribute($attribute_name, $values['type'], NULL, self::LANGUAGE_UNDETERMINED, $class);
        }
        else {
          $object->addAttribute($attribute_name, $values['type'], NULL);
        }

        $attribute = $object->getAttribute($attribute_name);
      }
      $value_property = (new \ReflectionClass($attribute))->getProperty('value');
      $value_property->setAccessible(TRUE);
      $value_property->setValue($attribute, $values['value']);
    }
    return $object;
  }

  /**
   * Static Factory method to format data from JSON into the CDFObject.
   *
   * @param string $json
   *   Data in JSON format.
   *
   * @return \Acquia\ContentHubClient\CDF\CDFObject
   *   CDFObject.
   *
   * @throws \ReflectionException
   */
  public static function fromJson(string $json) {
    return self::fromArray(json_decode($json, TRUE));
  }

  /**
   * {@inheritdoc}
   */
  public function getType() {
    return $this->type;
  }

  /**
   * {@inheritdoc}
   */
  public function getUuid() {
    return $this->uuid;
  }

  /**
   * {@inheritdoc}
   */
  public function getCreated() {
    return $this->created;
  }

  /**
   * {@inheritdoc}
   */
  public function getModified() {
    return $this->modified;
  }

  /**
   * {@inheritdoc}
   */
  public function getOrigin() {
    return $this->origin;
  }

  /**
   * {@inheritdoc}
   */
  public function getMetadata() {
    return $this->metadata;
  }

  /**
   * {@inheritdoc}
   */
  public function setMetadata(array $metadata) {
    $this->metadata = $metadata;
  }

  /**
   * {@inheritdoc}
   */
  public function getModuleDependencies() {
    return !empty($this->metadata['dependencies']['module']) ? $this->metadata['dependencies']['module'] : [];
  }

  /**
   * {@inheritdoc}
   */
  public function getDependencies() {
    return !empty($this->metadata['dependencies']['entity']) ? $this->metadata['dependencies']['entity'] : [];
  }

  /**
   * {@inheritdoc}
   */
  public function hasProcessedDependencies() {
    return $this->processed;
  }

  /**
   * {@inheritdoc}
   */
  public function markProcessedDependencies() {
    $this->processed = TRUE;
  }

  /**
   * {@inheritdoc}
   */
  public function getAttributes() {
    return $this->attributes;
  }

  /**
   * {@inheritdoc}
   */
  public function getAttribute($id) {
    if (!empty($this->attributes[$id])) {
      return $this->attributes[$id];
    }
  }

  /**
   * {@inheritdoc}
   */
  public function addAttribute(
    $id,
    $type,
    $value = NULL,
    $language = self::LANGUAGE_UNDETERMINED,
    $className = CDFAttribute::class
  ) {
    if ($className !== CDFAttribute::class && !is_subclass_of($className, CDFAttribute::class)) {
      throw new \Exception(sprintf("The %s class must be a subclass of \Acquia\ContentHubClient\CDFAttribute", $className));
    }
    $attribute = new $className($id, $type, $value, $language);
    $this->attributes[$attribute->getId()] = $attribute;
    // Keep track of the class used for this attribute.
    if ($className !== CDFAttribute::class) {
      $this->metadata['attributes'][$attribute->getId()]['class'] = $className;
    }
    else {
      unset($this->metadata['attributes'][$attribute->getId()]);
    }
  }

  /**
   * {@inheritdoc}
   */
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
