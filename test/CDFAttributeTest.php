<?php

namespace Acquia\ContentHubClient\test;

use Acquia\ContentHubClient\CDF\CDFObject;
use Acquia\ContentHubClient\CDFAttribute;
use PHPUnit\Framework\TestCase;

/**
 * Class CDFAttributeTest.
 *
 * @covers \Acquia\ContentHubClient\CDFAttribute
 *
 * @package Acquia\ContentHubClient\test
 */
class CDFAttributeTest extends TestCase {

  /**
   * Attribute data.
   *
   * @var array
   */
  private $attributeData;

  /**
   * Attribute ID.
   *
   * @var mixed
   */
  private $attributeId;

  /**
   * {@inheritDoc}
   */
  public function setUp(): void {
    $this->attributeData = $this->getAttributeData();
    $this->attributeId = $this->attributeData['attributes']['id'];
  }

  /**
   * {@inheritDoc}
   */
  public function tearDown(): void {
    unset($this->attributeId);
    unset($this->attributeData);
  }

  /**
   * @covers \Acquia\ContentHubClient\CDFAttribute::getType
   *
   * @throws \Exception
   */
  public function testCreateStringAttribute() {
    $title = $this->attributeData['attributes']['title'];

    $attribute = new CDFAttribute($this->attributeId, CDFAttribute::TYPE_STRING);
    $this->assertEquals('string', $attribute->getType());

    foreach ($title['value'] as $language => $value) {
      $attribute->setValue($value, $language);
    }

    $this->assertEquals($title['value'], $attribute->getValue());
  }

  /**
   * @covers \Acquia\ContentHubClient\CDFAttribute::getType
   *
   * @throws \Exception
   */
  public function testCreateNumericAttribute() {
    $numericAttribute = $this->attributeData['attributes']['num'];

    $attribute = new CDFAttribute($this->attributeId, CDFAttribute::TYPE_NUMBER);
    $this->assertEquals('number', $attribute->getType());

    foreach ($numericAttribute['value'] as $language => $value) {
      $attribute->setValue($value, $language);
    }

    $this->assertEquals($numericAttribute['value'], $attribute->getValue());
  }

  /**
   * @covers \Acquia\ContentHubClient\CDFAttribute
   * @throws \Exception
   */
  public function testAttributeToArrayConvert() {
    $value = $this->attributeData['attributes']['num']['value']['en'];

    $attribute = new CDFAttribute($this->attributeId, CDFAttribute::TYPE_NUMBER, $value);

    $expected = [
      'type' => CDFAttribute::TYPE_NUMBER,
      'value' => [CDFObject::LANGUAGE_UNDETERMINED => $value],
    ];

    $this->assertEquals($this->attributeId, $attribute->getId());
    $this->assertEquals($expected, $attribute->toArray());
  }

  /**
   * @covers \Acquia\ContentHubClient\CDFAttribute
   *
   * @throws \Exception
   */
  public function testCreateNumericArrayAttribute() {
    $numericArrayAttribute = $this->attributeData['attributes']['num_array'];

    $attribute = new CDFAttribute($this->attributeId, CDFAttribute::TYPE_ARRAY_NUMBER);
    $this->assertEquals('array<number>', $attribute->getType());

    foreach ($numericArrayAttribute['value'] as $language => $value) {
      $attribute->setValue($value, $language);
    }

    $this->assertEquals($numericArrayAttribute['value'], $attribute->getValue());
  }

  /**
   * @covers \Acquia\ContentHubClient\CDFAttribute
   */
  public function testUnsupportedDataTypeAttribute() {
    try {
      $dataType = 'unsupported_data_type';
      $attribute = new CDFAttribute($this->attributeId, $dataType);
      $this->fail(sprintf("It was expected an exception from \"%s\".", $dataType));
    }
    catch (\Exception $e) {
      $this->assertEquals(sprintf("Unsupported CDF Attribute data type \"%s\".", $dataType), $e->getMessage());
    }
  }

  /**
   * Provides test data.
   *
   * @return array
   *   Test data.
   */
  private function getAttributeData() {
    return [
      "attributes" => [
        "id" => 1,
        "num_array" => [
          "type" => "array<number>",
          "value" => [
            "en" => [
              6.66,
              3.23,
            ],
            "hu" => [
              4.66,
              4.23,
            ],
            "und" => [
              1.22,
              1.11,
            ],
          ],
        ],
        "num" => [
          "type" => "number",
          "value" => [
            'en' => 13.45,
            'es' => 1.43,
            CDFObject::LANGUAGE_UNDETERMINED => 1.23,
          ],
        ],
        "title" => [
          "type" => "string",
          "value" => [
            "en" => "nothing",
            "es" => "nada",
            CDFObject::LANGUAGE_UNDETERMINED => "niente",
          ],
        ],
      ],
    ];
  }

}
