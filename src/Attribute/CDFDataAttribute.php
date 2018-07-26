<?php


namespace Acquia\ContentHubClient\Attribute;


use Acquia\ContentHubClient\CDFAttribute;

class CDFDataAttribute extends CDFAttribute {

  public function __construct($id, $type, $value, $language = 'und') {
    $value = base64_encode($value);
    parent::__construct($id, $type, $value, $language);
  }

  public function setValue($value, $langauge = 'und') {
    $value = base64_encode($value);
    parent::setValue($value, $langauge);
  }

  public function getValue() {
    $values = parent::getValue();
    foreach ($values as $key => $value) {
      $values[$key] = base64_decode($value);
    }
    return $values;
  }

  /**
   * @return array
   */
  public function toArray() {
    return [
      'type' => $this->getType(),
      'value' => $this->value,
    ];
  }

}
