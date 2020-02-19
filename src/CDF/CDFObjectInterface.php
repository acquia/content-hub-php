<?php

namespace Acquia\ContentHubClient\CDF;

/**
 * Interface CDFObjectInterface.
 *
 * @package Acquia\ContentHubClient\CDF
 */
interface CDFObjectInterface {

  /**
   * Returns CDF object type.
   *
   * @return string
   *   Object type.
   */
  public function getType();

  /**
   * Returns CDF object UUID.
   *
   * @return string
   *   Object uuid.
   */
  public function getUuid();

  /**
   * Returns CDF created date.
   *
   * @return string
   *   Created date.
   */
  public function getCreated();

  /**
   * Returns modified date.
   *
   * @return string
   *   Modified date.
   */
  public function getModified();

  /**
   * Returns origin UUID.
   *
   * @return string
   *   Origin UUID.
   */
  public function getOrigin();

  /**
   * Returns object's metadata.
   *
   * @return array
   *   Object's metadata.
   */
  public function getMetadata();

  /**
   * Metadata setter.
   *
   * @param array $metadata
   *   Metadata array.
   */
  public function setMetadata(array $metadata);

  /**
   * Returns dependent modules.
   *
   * @return array
   *   Modules list.
   */
  public function getModuleDependencies();

  /**
   * Returns dependent entities.
   *
   * @return array
   *   Dependencies list.
   */
  public function getDependencies();

  /**
   * Checks that object has processed dependencies.
   *
   * @return bool
   *   TRUE if dependencies was processed, otherwise FALSE.
   */
  public function hasProcessedDependencies();

  /**
   * Mark that object have processed dependencies.
   */
  public function markProcessedDependencies();

  /**
   * Returns object's attributes.
   *
   * @return mixed
   *   Attributes list.
   */
  public function getAttributes();

  /**
   * Returns attribute by ID.
   *
   * @param string $id
   *   Attribute ID.
   *
   * @return \Acquia\ContentHubClient\CDFAttribute
   *   Attribute object.
   */
  public function getAttribute($id);

  /**
   * Appends attribute to object.
   *
   * @param string $id
   *   Attribute ID.
   * @param string $type
   *   Attribute type.
   * @param mixed|null $value
   *   Attribute value.
   * @param string $language
   *   Attribute language.
   * @param string $class
   *   Attribute class.
   */
  public function addAttribute(
    $id,
    $type,
    $value = NULL,
    $language = 'und',
    $class = '\Acquia\ContentHubClient\CDFAttribute'
  );

  /**
   * Converts object to array.
   *
   * @return mixed
   *   Array representation.
   */
  public function toArray();

}
