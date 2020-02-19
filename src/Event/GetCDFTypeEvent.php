<?php

namespace Acquia\ContentHubClient\Event;

use Acquia\ContentHubClient\CDF\CDFObjectInterface;
use Symfony\Component\EventDispatcher\Event;

/**
 * Class GetCDFTypeEvent.
 *
 * @package Acquia\ContentHubClient\Event
 */
class GetCDFTypeEvent extends Event {

  /**
   * The CDF representation from plexus.
   *
   * @var array
   */
  protected $data;

  /**
   * The CDF type.
   *
   * @var string
   */
  protected $type;

  /**
   * The instantiated object.
   *
   * @var \Acquia\ContentHubClient\CDF\CDFObjectInterface
   */
  protected $cdfObject;

  /**
   * GetCDFTypeEvent constructor.
   *
   * @param array $data
   *   Data.
   */
  public function __construct(array $data) {
    $this->data = $data;

    if (!isset($data['type'])) {
      throw new \InvalidArgumentException('Parameters should have a \'type\' key');
    }
    $this->type = $data['type'];
  }

  /**
   * Returns CDF object.
   *
   * @return \Acquia\ContentHubClient\CDF\CDFObjectInterface
   *   CDF object.
   */
  public function getObject() {
    return $this->cdfObject;
  }

  /**
   * CDF object setter.
   *
   * @param \Acquia\ContentHubClient\CDF\CDFObjectInterface $object
   *   CDF object.
   */
  public function setObject(CDFObjectInterface $object) {
    $this->cdfObject = $object;
  }

  /**
   * Returns event data.
   *
   * @return array
   *   CDF representation from plexus.
   */
  public function getData() {
    return $this->data;
  }

  /**
   * Returns CDF Type.
   *
   * @return string
   *   CDF Type.
   */
  public function getType() {
    return $this->type;
  }

}
