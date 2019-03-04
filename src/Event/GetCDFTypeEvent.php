<?php

namespace Acquia\ContentHubClient\Event;

use Acquia\ContentHubClient\CDF\CDFObjectInterface;
use Symfony\Component\EventDispatcher\Event;

class GetCDFTypeEvent extends Event {


  /**
   * The CDF representation from plexus.
   * @var array data
   */
  protected $data;

  /**
   * @var string type
   * The CDF Type
   */
  protected $type;

  /**
   * @var CDFObjectInterface $cdfObject
   * The instantiated Object
   */
  protected $cdfObject;

  /**
   * GetCDFTypeEvent constructor.
   *
   * @param array $data
   * @throws \Exception
   */
  public function __construct(array $data) {
    $this->data = $data;
    // Should throw something if type is missing.
    if(isset($data['type'])) {
      $this->type = $data['type'];
    } else {
      throw new \Exception('No type specified.');
    }
  }

  public function getObject() {
    return $this->cdfObject;
  }

  public function setObject(CDFObjectInterface $object) {
    $this->cdfObject = $object;
  }

  /**
   * @return array
   */
  public function getData() {
    return $this->data;
  }

  /**
   * @return string
   */
  public function getType() {
    return $this->type;
  }
}
