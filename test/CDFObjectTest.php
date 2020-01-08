<?php

namespace Acquia\ContentHubClient\test;

use Acquia\ContentHubClient\CDF\CDFObject;
use Acquia\ContentHubClient\CDFAttribute;
use Acquia\ContentHubClient\assets\CDFAttributeChild;
use PHPUnit\Framework\TestCase;

/**
 * Class CDFObjectTest.
 *
 * @covers \Acquia\ContentHubClient\CDF\CDFObject
 *
 * @package Acquia\ContentHubClient\test
 */
class CDFObjectTest extends TestCase {

  /**
   * CDFObject instance.
   *
   * @var \Acquia\ContentHubClient\CDF\CDFObject
   */
  private $cdfObject;

  /**
   * {@inheritDoc}
   */
  public function setUp(): void {
    parent::setUp();
    $objectParameters = $this->getObjectData();
    $this->cdfObject = new CDFObject(
      $objectParameters['type'],
      $objectParameters['uuid'],
      $objectParameters['created'],
      $objectParameters['modified'],
      $objectParameters['origin'],
      $objectParameters['metadata']
    );
  }

  /**
   * {@inheritDoc}
   */
  public function tearDown(): void {
    parent::tearDown();
    unset($this->cdfObject);
  }

  /**
   * @covers \Acquia\ContentHubClient\CDF\CDFObject::toArray
   *
   * @dataProvider objectDataProvider
   *
   * @param array $objectData
   *   Test data.
   */
  public function testToArray(array $objectData) {
    $this->assertEquals($this->cdfObject->toArray(), $objectData);
  }

  /**
   * @covers \Acquia\ContentHubClient\CDF\CDFObject::getUuid
   *
   * @dataProvider objectDataProvider
   *
   * @param array $settingsData
   *   Test data.
   */
  public function testGetUuid(array $settingsData) {
    $emptyObject = new CDFObject('', '', '', '', '');
    $this->assertEmpty($emptyObject->getUuid());
    $this->assertEquals($this->cdfObject->getUuid(), $settingsData['uuid']);
  }

  /**
   * @covers \Acquia\ContentHubClient\CDF\CDFObject::getType
   *
   * @dataProvider objectDataProvider
   *
   * @param array $settingsData
   *   Test data.
   */
  public function testGetType(array $settingsData) {
    $this->assertEquals($this->cdfObject->getType(), $settingsData['type']);
    $this->assertNotEquals($this->cdfObject->getType(), 'wrong_type');
  }

  /**
   * @covers \Acquia\ContentHubClient\CDF\CDFObject::getCreated
   *
   * @dataProvider objectDataProvider
   *
   * @param array $settingsData
   *   Test data.
   */
  public function testGetCreated(array $settingsData) {
    $this->assertEquals($this->cdfObject->getCreated(),
      $settingsData['created']);
    $this->assertNotEquals($this->cdfObject->getCreated(), 'wrong_date');
    $this->assertNotEquals($this->cdfObject->getCreated(),
      $settingsData['modified']);
  }

  /**
   * @covers \Acquia\ContentHubClient\CDF\CDFObject::getModified
   *
   * @dataProvider objectDataProvider
   *
   * @param array $settingsData
   *   Test data.
   */
  public function testGetModified(array $settingsData) {
    $this->assertEquals($this->cdfObject->getModified(), $settingsData['modified']);
    $this->assertNotEquals($this->cdfObject->getModified(), 'wrong_date');
    $this->assertNotEquals($this->cdfObject->getModified(), $settingsData['created']);
  }

  /**
   * @covers \Acquia\ContentHubClient\CDF\CDFObject::getOrigin
   *
   * @dataProvider objectDataProvider
   *
   * @param array $settingsData
   *   Test data.
   */
  public function testGetOrigin(array $settingsData) {
    $this->assertEquals($this->cdfObject->getOrigin(), $settingsData['origin']);
    $this->assertNotEquals($this->cdfObject->getModified(), '33333333-00000000-00000000-00000000');
  }

  /**
   * @covers \Acquia\ContentHubClient\CDF\CDFObject::getMetadata
   *
   * @dataProvider objectDataProvider
   *
   * @param array $settingsData
   *   Test data.
   */
  public function testGetMetadata(array $settingsData) {
    $this->assertEquals($this->cdfObject->getMetadata(), $settingsData['metadata']);
    $this->assertNotEquals($this->cdfObject->getMetadata(), []);
  }

  /**
   * @covers \Acquia\ContentHubClient\CDF\CDFObject::setMetadata
   *
   * @dataProvider objectDataProvider
   *
   * @param array $settingsData
   *   Test data.
   */
  public function testSetMetadata(array $settingsData) {
    $oldMetadata = $settingsData['metadata'];
    $newMetadata = [
      'http://new1' => '77777777-0000-0000-0000-000000000000',
      'http://new2' => '88888888-0000-0000-0000-000000000000',
    ];
    $this->assertEquals($this->cdfObject->getMetadata(), $oldMetadata);
    $this->cdfObject->setMetadata($newMetadata);
    $this->assertEquals($this->cdfObject->getMetadata(), $newMetadata);
    $this->assertNotEquals($this->cdfObject->getMetadata(), $oldMetadata);
  }

  /**
   * @covers \Acquia\ContentHubClient\CDF\CDFObject::getModuleDependencies
   */
  public function testGetModuleDependencies() {
    $moduleValue = 'module_value';
    $this->cdfObject->setMetadata([]);
    $this->assertEquals($this->cdfObject->getModuleDependencies(), []);
    $this->cdfObject->setMetadata([
      'dependencies' => [
        'module' => $moduleValue,
      ],
    ]);
    $this->assertEquals($this->cdfObject->getModuleDependencies(), $moduleValue);
  }

  /**
   * @covers \Acquia\ContentHubClient\CDF\CDFObject::getDependencies
   */
  public function testGetDependencies() {
    $entityValue = 'entity_value';
    $this->cdfObject->setMetadata([]);
    $this->assertEquals($this->cdfObject->getDependencies(), []);
    $this->cdfObject->setMetadata([
      'dependencies' => [
        'entity' => $entityValue,
      ],
    ]);
    $this->assertEquals($this->cdfObject->getDependencies(), $entityValue);
  }

  /**
   * @covers \Acquia\ContentHubClient\CDF\CDFObject::hasProcessedDependencies
   */
  public function testProcessedDependencies() {
    $this->assertFalse($this->cdfObject->hasProcessedDependencies());
    $this->cdfObject->markProcessedDependencies();
    $this->assertTrue($this->cdfObject->hasProcessedDependencies());
  }

  /**
   * @covers \Acquia\ContentHubClient\CDF\CDFObject::addAttribute
   */
  public function testAddIncorrectAttribute() {
    try {
      $dummyClassName = 'DummyClass';
      $this->cdfObject->addAttribute(
        'dummy_attribute_id',
        CDFAttribute::TYPE_ARRAY_BOOLEAN,
        [],
        CDFObject::LANGUAGE_UNDETERMINED,
        $dummyClassName
      );
      $this->fail(sprintf("It was expected an exception with \"%s\" class.",
        $dummyClassName));
    }
    catch (\Exception $e) {
      $this->assertEquals(sprintf("The %s class must be a subclass of \Acquia\ContentHubClient\CDFAttribute",
        $dummyClassName), $e->getMessage());
    }
  }

  /**
   * @covers \Acquia\ContentHubClient\CDF\CDFObject::addAttribute
   *
   * @dataProvider attributeDataProvider
   *
   * @param array $value
   *   Test data.
   */
  public function testAddAttribute(array $value) {
    $this->assertEquals($this->cdfObject->getAttributes(), []);

    try {
      $this->cdfObject->addAttribute(
        'attribute_id_1',
        CDFAttribute::TYPE_ARRAY_INTEGER,
        $value,
        CDFObject::LANGUAGE_UNDETERMINED,
        CDFAttribute::class
      );
      $this->assertEquals(
        $this->cdfObject->getAttribute('attribute_id_1'),
        (new CDFAttribute('attribute_id_1', CDFAttribute::TYPE_ARRAY_INTEGER, $value, CDFObject::LANGUAGE_UNDETERMINED))
      );
    }
    catch (\Exception $exception) {
    }
  }

  /**
   * @covers \Acquia\ContentHubClient\CDF\CDFObject::addAttribute
   *
   * @dataProvider attributeDataProvider
   *
   * @param array $value
   *   Test data.
   */
  public function testAddAttributeWithChildClass(array $value) {
    try {
      $this->cdfObject->addAttribute(
        'child_attribute_id_1',
        CDFAttribute::TYPE_ARRAY_INTEGER,
        $value[CDFObject::LANGUAGE_UNDETERMINED],
        CDFObject::LANGUAGE_UNDETERMINED,
        CDFAttributeChild::class
      );
      $this->assertEquals(
        $this->cdfObject->getAttribute('child_attribute_id_1')->getValue(),
        $value[CDFObject::LANGUAGE_UNDETERMINED]
      );
      $this->assertEquals(
        $this->cdfObject->getMetadata()['attributes']['child_attribute_id_1']['class'],
        CDFAttributeChild::class
      );
    }
    catch (\Exception $exception) {
    }
  }

  /**
   * Returns test object' data.
   *
   * @return array
   *   Test data.
   */
  public function getObjectData() {
    return [
      'type' => 'product',
      'uuid' => '11111111-00000000-00000000-00000000',
      'created' => '2014-12-21T20:12:11+00:00Z',
      'modified' => '2015-12-21T20:12:11+00:00Z',
      'origin' => '22222222-0000-0000-0000-000000000000',
      'metadata' => [
        'http://example1.com/webhooks' => '00000000-0000-0000-0000-000000000000',
        'http://example2.com/webhooks' => '11111111-0000-0000-0000-000000000000',
      ],
    ];
  }

  /**
   * Data provider.
   *
   * @return array
   *   Test data.
   */
  public function attributeDataProvider() {
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

  /**
   * Data provider.
   *
   * @return array
   *   Test data.
   */
  public function objectDataProvider() {
    return [
      [
        $this->getObjectData(),
      ],
    ];
  }

}
