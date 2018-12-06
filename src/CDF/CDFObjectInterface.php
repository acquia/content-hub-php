<?php

namespace Acquia\ContentHubClient\CDF;

interface CDFObjectInterface {

  /**
   * @return string
   */
  public function getType();

  /**
   * @return string
   */
  public function getUuid();

  /**
   * @return string
   */
  public function getCreated();

  /**
   * @return string
   */
  public function getModified();

  /**
   * @return string
   */
  public function getOrigin();

  /**
   * @return array
   */
  public function getMetadata();

  public function setMetadata(array $metadata);

  public function getModuleDependencies();

  public function getDependencies();

  public function hasProcessedDependencies();

  public function markProcessedDependencies();

  public function getAttributes();

  /**
   * @param $id
   *
   * @return \Acquia\ContentHubClient\CDFAttribute
   */
  public function getAttribute($id);

  public function addAttribute($id, $type, $value = NULL, $language = 'und', $class = '\Acquia\ContentHubClient\CDFAttribute');

  public function toArray();

}