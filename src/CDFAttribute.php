<?php

namespace Acquia\ContentHubClient;

use ReflectionClass;

class CDFAttribute {

  /**
   *  Attribute's data types.
   */
  const TYPE_INTEGER         = 'integer';
  const TYPE_STRING          = 'string';
  const TYPE_KEYWORD         = 'keyword';
  const TYPE_BOOLEAN         = 'boolean';
  const TYPE_NUMBER          = 'number';
  const TYPE_REFERENCE       = 'reference';
  const TYPE_OBJECT          = 'object';
  const TYPE_ARRAY_INTEGER   = 'array<integer>';
  const TYPE_ARRAY_STRING    = 'array<string>';
  const TYPE_ARRAY_KEYWORD   = 'array<keyword>';
  const TYPE_ARRAY_BOOLEAN   = 'array<boolean>';
  const TYPE_ARRAY_NUMBER    = 'array<number>';
  const TYPE_ARRAY_REFERENCE = 'array<reference>';

  /**
   * @var string
   */
  protected $id;

  /**
   * @var string
   */
  protected $type;

  /**
   * @var mixed
   */
  protected $value;

  /**
   * CDFAttribute constructor.
   *
   * @param string $id
   *   The identifier of the attribute.
   * @param string $type
   *   The attribute's data type.
   * @param mixed $value
   *   The value of the attribute.
   * @param string $language
   *   The language of the initial value.
   *
   * @throws \Exception Unsupported data type exception.
   */
  public function __construct($id, $type, $value = NULL, $language = CDFObject::LANGUAGE_UNDETERMINED) {
    $r = new ReflectionClass(__CLASS__);
    if (!in_array($type, $r->getConstants())) {
      // @todo validate value against data type?
      throw new \Exception(sprintf("Unsupported CDF Attribute data type \"%s\".", $type));
    }
    $this->id = $id;
    $this->type = $type;
    if ($value) {
      $this->value[$language] = $value;
    }
  }

  /**
   * @return string
   */
  public function getId() {
    return $this->id;
  }

  /**
   * @return string
   */
  public function getType() {
    return $this->type;
  }

  /**
   * @return mixed
   */
  public function getValue() {
    return $this->value;
  }

  public function setValue($value, $langauge = CDFObject::LANGUAGE_UNDETERMINED) {
    $this->value[$langauge] = $value;
  }

  /**
   * @return array
   */
  public function toArray() {
    return [
      'type' => $this->getType(),
      'value' => $this->getValue(),
    ];
  }

}
