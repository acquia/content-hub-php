<?php

namespace Acquia\ContentHubClient\test\CDF;


use Acquia\ContentHubClient\CDF\CDFObject;
use Acquia\ContentHubClient\CDFAttribute;
use Acquia\ContentHubClient\assets\CDFAttributeChild;
use phpDocumentor\Reflection\Types\Self_;
use PHPUnit\Framework\TestCase;

class CDFObjectTest extends TestCase {

  private const SAMPLE_CDF_OBJECT_PARAMS = [
    'type' => 'some-type',
    'uuid' => 'some-uuid',
    'created' => 'some-creation-date',
    'modified' => 'some-modification-date',
    'origin' => 'some-origin',
    'attributes' => [
      'name1' => [
        'type' => 'integer',
        'value' => 123,
      ],
      'name2' => [
        'type' => 'string',
        'value' => 'some-attribute-value',
      ],
    ],
    'metadata' => [
      'webhook-1' => 'w1-uuid',
      'webhook-2' => 'w2-uuid',
      'attributes' => [
        'name2' => [
          'class' => CDFAttributeChild::class,
        ],
      ],
    ],
  ];

  /**
   * @var CDFObject
   */
  private $cdfObject;

  public function setUp(): void {
    parent::setUp();

    $this->cdfObject = new CDFObject(
      self::SAMPLE_CDF_OBJECT_PARAMS['type'],
      self::SAMPLE_CDF_OBJECT_PARAMS['uuid'],
      self::SAMPLE_CDF_OBJECT_PARAMS['created'],
      self::SAMPLE_CDF_OBJECT_PARAMS['modified'],
      self::SAMPLE_CDF_OBJECT_PARAMS['origin'],
      self::SAMPLE_CDF_OBJECT_PARAMS['metadata']
    );
  }

  public function tearDown(): void {
    parent::tearDown();
    unset($this->cdfObject);
  }

  public function testToArray(): void {
    $this->cdfObject->addAttribute('id', CDFAttribute::TYPE_ARRAY_INTEGER, 1, CDFObject::LANGUAGE_UNDETERMINED, CDFAttribute::class);
    $base_array = self::SAMPLE_CDF_OBJECT_PARAMS;
    $base_array['attributes'] = $this->attributesToArray($this->cdfObject->getAttributes());

    $this->assertEquals($this->cdfObject->toArray(), $base_array);
  }

  public function testFromArrayCreation(): void {
    $cdf_object = CDFObject::fromArray(self::SAMPLE_CDF_OBJECT_PARAMS);

    $attributes = $cdf_object->getAttributes();

    $this->assertInstanceOf(CDFAttribute::class, $attributes['name1']);
    $this->assertInstanceOf(CDFAttributeChild::class, $attributes['name2']);
  }

  public function testFromJSONStringCreation(): void {
    $cdf_object = CDFObject::fromJson(json_encode(self::SAMPLE_CDF_OBJECT_PARAMS));

    $this->assertEquals(
      self::SAMPLE_CDF_OBJECT_PARAMS['attributes'],
      $this->attributesToArray($cdf_object->getAttributes())
    );
  }

  public function testGetAttributeReturnsNullIfNoAttributeIsPresent(): void {
    $this->assertNull($this->cdfObject->getAttribute('some-id'));
  }

  public function testGetModuleDependenciesReturnsEmptyArrayWhenEmpty(): void {
    $this->assertEquals($this->cdfObject->getModuleDependencies(), []);
  }

  public function testGetModuleDependenciesReturnsNoEmptyArrayWhenNonEmpty(): void {
    $some_value = 'some-value';
    $this->cdfObject->setMetadata([
      'dependencies' => [
        'module' => $some_value,
      ],
    ]);

    $this->assertEquals($this->cdfObject->getModuleDependencies(), $some_value);
  }

  public function testGetDependenciesReturnsEmptyArrayWhenEmpty(): void {
    $this->assertEquals($this->cdfObject->getDependencies(), []);
  }

  public function testGetDependenciesReturnsNoEmptyArrayWhenNonEmpty(): void {
    $some_value = 'some-value';
    $this->cdfObject->setMetadata([
      'dependencies' => [
        'entity' => $some_value,
      ],
    ]);

    $this->assertEquals($this->cdfObject->getDependencies(), $some_value);
  }

  public function testProcessedDependencies(): void {
    $this->assertFalse($this->cdfObject->hasProcessedDependencies());
    $this->cdfObject->markProcessedDependencies();
    $this->assertTrue($this->cdfObject->hasProcessedDependencies());
  }

  public function testAddIncorrectAttributeThrowsException(): void {
    $this->expectException(\Exception::class);
    $this->cdfObject->addAttribute('dummy_attribute_id', CDFAttribute::TYPE_ARRAY_BOOLEAN, [], CDFObject::LANGUAGE_UNDETERMINED, 'DummyClass');
  }

  /**
   * @dataProvider attributeDataProvider
   *
   * @param $value
   *
   * @throws \Exception
   */
  public function testAddAttributeAltersMetadataWithCDFAttributeSubclasses($value): void {
    $attribute_id = 'attribute_id_1';
    $cdf_attribute_child_class = CDFAttributeChild::class;

    $this->cdfObject->addAttribute($attribute_id, CDFAttribute::TYPE_ARRAY_INTEGER, $value, CDFObject::LANGUAGE_UNDETERMINED, $cdf_attribute_child_class);

    $this->assertEquals(get_class($this->cdfObject->getAttribute($attribute_id)), $cdf_attribute_child_class);
    $this->assertTrue(isset($this->cdfObject->getMetadata()['attributes'][$attribute_id]));
    $this->assertEquals($this->cdfObject->getMetadata()['attributes'][$attribute_id]['class'], $cdf_attribute_child_class);
  }

  /**
   * @dataProvider attributeDataProvider
   *
   * @param $value
   *
   * @throws \Exception
   */
  public function testAddAttributeUnsetsAttributeFromMetadataWithCDFAttributeClass($value): void {
    $attribute_id = 'attribute_id_1';
    $this->cdfObject->addAttribute($attribute_id, CDFAttribute::TYPE_ARRAY_INTEGER, $value, CDFObject::LANGUAGE_UNDETERMINED, CDFAttribute::class);

    $this->assertFalse(isset($this->cdfObject->getMetadata()['attributes'][$attribute_id]));
  }

  public function attributeDataProvider(): array {
    return [
      [
        'value' => [
          'en' => [
            6.66,
            3.23,
          ],
          'hu' => [
            4.66,
            4.23,
          ],
          CDFObject::LANGUAGE_UNDETERMINED => [
            1.22,
            1.11,
          ],
        ],
      ],
    ];
  }

  private function attributesToArray(array $attributes) {
    return array_map(static function (CDFAttribute $attribute) {
      return $attribute->toArray();
    }, $attributes);
  }

}
