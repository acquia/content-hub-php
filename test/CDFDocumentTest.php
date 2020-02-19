<?php

namespace Acquia\ContentHubClient\test;

use Acquia\ContentHubClient\CDF\CDFObject;
use Acquia\ContentHubClient\CDFDocument;
use PHPUnit\Framework\TestCase;

/**
 * Class CDFDocumentTest.
 *
 * @covers \Acquia\ContentHubClient\CDFDocument
 *
 * @package Acquia\ContentHubClient\test
 */
class CDFDocumentTest extends TestCase {

  /**
   * CDF document.
   *
   * @var \Acquia\ContentHubClient\CDFDocument
   */
  protected $cdfDocument;

  /**
   * {@inheritDoc}
   */
  public function setUp(): void {
    parent::setUp();
    $this->cdfDocument = new CDFDocument();
  }

  /**
   * {@inheritDoc}
   */
  public function tearDown(): void {
    parent::tearDown();
    unset($this->cdfDocument);
  }

  /**
   * @covers \Acquia\ContentHubClient\CDFDocument::hasEntities
   *
   * @dataProvider providerEntityOperations
   *
   * @param \Acquia\ContentHubClient\CDF\CDFObject $objectToAdd
   *   CDF object.
   * @param \Acquia\ContentHubClient\CDF\CDFObject $notAddedObject
   *   CDF object.
   */
  public function testHasEntities(CDFObject $objectToAdd, CDFObject $notAddedObject) {
    // Check hasEntities method before and after we add an Object.
    $this->assertEquals($this->cdfDocument->hasEntities(), FALSE);
    $this->cdfDocument->addCdfEntity($objectToAdd);
    $this->assertEquals($this->cdfDocument->hasEntities(), TRUE);
  }

  /**
   * @covers \Acquia\ContentHubClient\CDFDocument::getCdfEntity
   *
   * @dataProvider providerEntityOperations
   *
   * @param \Acquia\ContentHubClient\CDF\CDFObject $objectToAdd
   *   CDF object.
   * @param \Acquia\ContentHubClient\CDF\CDFObject $notAddedObject
   *   CDF object.
   */
  public function testGetEntity(CDFObject $objectToAdd, CDFObject $notAddedObject) {
    // Check getting added and not added Object.
    $this->cdfDocument->addCdfEntity($objectToAdd);
    $this->assertEquals($this->cdfDocument->getCdfEntity($objectToAdd->getUuid()), $objectToAdd);
    $this->assertEquals($this->cdfDocument->getCdfEntity($notAddedObject->getUuid()), NULL);
  }

  /**
   * @covers \Acquia\ContentHubClient\CDFDocument::getEntities
   *
   * @dataProvider providerEntityOperations
   *
   * @param \Acquia\ContentHubClient\CDF\CDFObject $objectOne
   *   CDF object.
   * @param \Acquia\ContentHubClient\CDF\CDFObject $objectTwo
   *   CDF object.
   */
  public function testGetEntities(CDFObject $objectOne, CDFObject $objectTwo) {
    // Check getting added and not added Object.
    $this->cdfDocument->setCdfEntities($objectOne, $objectTwo);

    foreach ($this->cdfDocument->getEntities() as $entity) {
      $this->assertInstanceOf(CDFObject::class, $entity);
    }
  }

  /**
   * @covers \Acquia\ContentHubClient\CDFDocument::addCdfEntity
   *
   * @dataProvider providerEntityOperations
   *
   * @param \Acquia\ContentHubClient\CDF\CDFObject $objectToAdd
   *   CDF object.
   * @param \Acquia\ContentHubClient\CDF\CDFObject $notAddedObject
   *   CDF object.
   */
  public function testAddEntity(CDFObject $objectToAdd, CDFObject $notAddedObject) {
    // Check if hasEntity will return correct values for added and not added Objects.
    $this->cdfDocument->addCdfEntity($objectToAdd);
    $this->assertEquals($this->cdfDocument->hasEntity($objectToAdd->getUuid()), TRUE);
    $this->assertEquals($this->cdfDocument->hasEntity($notAddedObject->getUuid()), FALSE);
  }

  /**
   * @covers \Acquia\ContentHubClient\CDFDocument::removeCdfEntity
   *
   * @dataProvider providerEntityOperations
   *
   * @param \Acquia\ContentHubClient\CDF\CDFObject $objectToAdd
   *   CDF object.
   */
  public function testRemoveEntity(CDFObject $objectToAdd) {
    // Test removing Entity.
    $this->cdfDocument->addCdfEntity($objectToAdd);
    $this->assertEquals($this->cdfDocument->hasEntity($objectToAdd->getUuid()), TRUE);
    $this->cdfDocument->removeCdfEntity($objectToAdd->getUuid());
    $this->assertEquals($this->cdfDocument->hasEntity($objectToAdd->getUuid()), FALSE);
  }

  /**
   * @covers \Acquia\ContentHubClient\CDFDocument::mergeDocuments
   *
   * @dataProvider providerMergeDocuments
   *
   * @param array $setOne
   *   Data set.
   * @param array $setTwo
   *   Data set.
   */
  public function testMergeDocuments(array $setOne, array $setTwo) {
    $this->cdfDocument->setCdfEntities(...$setOne);
    $documentToMerge = new CDFDocument(...$setTwo);

    foreach ($setTwo as $entity) {
      $this->assertFalse($this->cdfDocument->hasEntity($entity->getUuid()));
    }
    $this->cdfDocument->mergeDocuments($documentToMerge);
    foreach ($setTwo as $entity) {
      $this->assertTrue($this->cdfDocument->hasEntity($entity->getUuid()));
    }
  }

  /**
   * @covers \Acquia\ContentHubClient\CDFDocument::mergeDocuments
   *
   * @dataProvider providerMergeDocuments
   *
   * @param array $setOne
   *   Data set.
   * @param array $setTwo
   *   Data set.
   */
  public function testMergeDocumentsByKeys(array $setOne, array $setTwo) {
    $this->cdfDocument->setCdfEntities(...$setOne);
    $documentToMerge = new CDFDocument(...$setTwo);

    $keysOne = array_keys($this->cdfDocument->getEntities());
    $keysTwo = array_keys($documentToMerge->getEntities());

    $this->assertEquals(array_diff($keysOne, $keysTwo), $keysOne);

    $this->cdfDocument->mergeDocuments($documentToMerge);
    $mergedKeys = array_keys($this->cdfDocument->getEntities());
    $this->assertEquals($mergedKeys, array_merge($keysOne, $keysTwo));
  }

  /**
   * @covers \Acquia\ContentHubClient\CDFDocument::mergeDocuments
   *
   * @dataProvider providerMergeDocumentsNoOverlap
   *
   * @param array $setOne
   *   Data set.
   * @param array $setTwo
   *   Data set.
   * @param \Acquia\ContentHubClient\CDF\CDFObject $elementFromSetTwo
   *   CDF object.
   */
  public function testMergeDocumentsNoOverlap(array $setOne, array $setTwo, CDFObject $elementFromSetTwo) {
    $this->cdfDocument->setCdfEntities(...$setOne);
    $documentToMerge = new CDFDocument(...$setTwo);

    $this->assertFalse($this->cdfDocument->hasEntity($elementFromSetTwo->getUuid()));
    $this->cdfDocument->mergeDocuments($documentToMerge);
    $this->assertTrue($this->cdfDocument->hasEntity($elementFromSetTwo->getUuid()));
  }

  /**
   * @covers \Acquia\ContentHubClient\CDFDocument::mergeDocuments
   *
   * @dataProvider providerMergeDocumentsOverlap
   *
   * @param array $setOne
   *   Data set.
   * @param array $setTwo
   *   Data set.
   * @param \Acquia\ContentHubClient\CDF\CDFObject $overlappingElement
   *   CDF object.
   */
  public function testMergeDocumentsOverlap(array $setOne, array $setTwo, CDFObject $overlappingElement) {
    $this->cdfDocument->setCdfEntities(...$setOne);
    $documentToMerge = new CDFDocument(...$setTwo);

    $this->assertTrue($this->cdfDocument->hasEntity($overlappingElement->getUuid()));
    $this->cdfDocument->mergeDocuments($documentToMerge);
    $this->assertTrue($this->cdfDocument->hasEntity($overlappingElement->getUuid()));
  }

  /**
   * @covers \Acquia\ContentHubClient\CDFDocument::toString
   *
   * @dataProvider providerToString
   *
   * @param array $objectsList
   *   Objects list.
   * @param string $emptyObjectsJson
   *   JSON string.
   * @param string $filledObjectsJson
   *   JSON string.
   */
  public function testToString(array $objectsList, $emptyObjectsJson, $filledObjectsJson) {
    $this->assertJsonStringEqualsJsonString($this->cdfDocument->toString(), $emptyObjectsJson);
    $this->cdfDocument->setCdfEntities(...$objectsList);
    $this->assertJsonStringEqualsJsonString($this->cdfDocument->toString(), $filledObjectsJson);
  }

  /**
   * Data provider for ::testHasEntities.
   *
   * @return array
   *   Test data.
   */
  public function providerEntityOperations() {
    $cdfObjectMockFirst = $this->getMockBuilder(CDFObject::class)
      ->disableOriginalConstructor()
      ->setMethods(['getUuid'])
      ->getMock();
    $cdfObjectMockSecond = $this->getMockBuilder(CDFObject::class)
      ->disableOriginalConstructor()
      ->setMethods(['getUuid'])
      ->getMock();

    $cdfObjectMockFirst->expects($this->any())
      ->method('getUuid')
      ->will($this->returnValue('11111111-0000-0000-0000-000000000000'));

    $cdfObjectMockSecond->expects($this->any())
      ->method('getUuid')
      ->will($this->returnValue('22222222-0000-0000-0000-000000000000'));

    return [
      [
        $cdfObjectMockFirst,
        $cdfObjectMockSecond,
      ],
    ];
  }

  /**
   * Data provider for ::testMergeDocuments.
   *
   * @return array
   *   Test data.
   */
  public function providerMergeDocuments() {
    $cdfObjectMockFirst = $this->getMockBuilder(CDFObject::class)
      ->disableOriginalConstructor()
      ->setMethods(['getUuid'])
      ->getMock();
    $cdfObjectMockSecond = $this->getMockBuilder(CDFObject::class)
      ->disableOriginalConstructor()
      ->setMethods(['getUuid'])
      ->getMock();

    $cdfObjectMockFirst->expects($this->any())
      ->method('getUuid')
      ->willReturn('33333333-0000-0000-0000-000000000000');

    $cdfObjectMockSecond->expects($this->any())
      ->method('getUuid')
      ->willReturn('44444444-0000-0000-0000-000000000000');

    return [
      array_merge($this->providerEntityOperations(), [
        [
          $cdfObjectMockFirst,
          $cdfObjectMockSecond,
        ],
      ]),
    ];
  }

  /**
   * Data provider for ::testMergeDocumentsNoOverlap.
   *
   * @return array
   *   Test data.
   */
  public function providerMergeDocumentsNoOverlap() {
    // First set of objects.
    $setOneFirst = $this->getMockBuilder(CDFObject::class)
      ->disableOriginalConstructor()
      ->setMethods(['getUuid'])
      ->getMock();
    $setOneSecond = $this->getMockBuilder(CDFObject::class)
      ->disableOriginalConstructor()
      ->setMethods(['getUuid'])
      ->getMock();

    $setOneFirst->expects($this->any())
      ->method('getUuid')
      ->willReturn('11111111-0000-0000-0000-000000000000');

    $setOneSecond->expects($this->any())
      ->method('getUuid')
      ->willReturn('22222222-0000-0000-0000-000000000000');

    // Second set of objects.
    $setTwoFirst = $this->getMockBuilder(CDFObject::class)
      ->disableOriginalConstructor()
      ->setMethods(['getUuid'])
      ->getMock();
    $setTwoSecond = $this->getMockBuilder(CDFObject::class)
      ->disableOriginalConstructor()
      ->setMethods(['getUuid'])
      ->getMock();

    $setTwoFirst->expects($this->any())
      ->method('getUuid')
      ->willReturn('33333333-0000-0000-0000-000000000000');

    $setTwoSecond->expects($this->any())
      ->method('getUuid')
      ->willReturn('44444444-0000-0000-0000-000000000000');

    return [
      [
        [
          $setOneFirst,
          $setOneSecond,
        ],
        [
          $setTwoFirst,
          $setTwoSecond,
        ],
        $setTwoFirst,
      ],
      [
        [
          $setOneFirst,
          $setOneSecond,
        ],
        [
          $setTwoFirst,
          $setTwoSecond,
        ],
        $setTwoSecond,
      ],
    ];
  }

  /**
   * Data provider for ::testMergeDocumentsOverlap.
   *
   * @return array
   *   Test data.
   */
  public function providerMergeDocumentsOverlap() {
    // First set of objects.
    $setOneFirst = $this->getMockBuilder(CDFObject::class)
      ->disableOriginalConstructor()
      ->setMethods(['getUuid'])
      ->getMock();
    $setOneSecond = $this->getMockBuilder(CDFObject::class)
      ->disableOriginalConstructor()
      ->setMethods(['getUuid'])
      ->getMock();

    $setOneFirst->expects($this->any())
      ->method('getUuid')
      ->willReturn('11111111-0000-0000-0000-000000000000');

    $setOneSecond->expects($this->any())
      ->method('getUuid')
      ->willReturn('22222222-0000-0000-0000-000000000000');

    // Second set of objects.
    $setTwoFirst = $this->getMockBuilder(CDFObject::class)
      ->disableOriginalConstructor()
      ->setMethods(['getUuid'])
      ->getMock();
    $setTwoSecond = $this->getMockBuilder(CDFObject::class)
      ->disableOriginalConstructor()
      ->setMethods(['getUuid'])
      ->getMock();

    $setTwoFirst->expects($this->any())
      ->method('getUuid')
      ->willReturn('11111111-0000-0000-0000-000000000000');

    $setTwoSecond->expects($this->any())
      ->method('getUuid')
      ->willReturn('22222222-0000-0000-0000-000000000000');

    return [
      [
        [
          $setOneFirst,
          $setOneSecond,
        ],
        [
          $setTwoFirst,
          $setTwoSecond,
        ],
        $setTwoFirst,
      ],
      [
        [
          $setOneFirst,
          $setOneSecond,
        ],
        [
          $setTwoFirst,
          $setTwoSecond,
        ],
        $setTwoSecond,
      ],
    ];
  }

  /**
   * Data provider for ::testToString.
   *
   * @return array
   *   Test data.
   */
  public function providerToString() {
    $cdfObjectMockFirst = $this->getMockBuilder(CDFObject::class)
      ->disableOriginalConstructor()
      ->setMethods(['getUuid', 'toArray'])
      ->getMock();
    $cdfObjectMockSecond = $this->getMockBuilder(CDFObject::class)
      ->disableOriginalConstructor()
      ->setMethods(['getUuid', 'toArray'])
      ->getMock();

    $cdfObjectMockFirst->expects($this->any())
      ->method('getUuid')
      ->will($this->returnValue('55555555-0000-0000-0000-000000000000'));

    $cdfArrayMock = [
      'uuid' => '00000000-0000-0000-0000-000000000000',
      'type' => 'product',
      'created' => '2014-12-21T20:12:11+00:00Z',
      'modified' => '2014-12-21T20:12:11+00:00Z',
      'origin' => '00000000-0000-0000-0000-000000000000',
    ];
    $cdfObjectMockFirstToArray = $cdfArrayMock;
    $cdfObjectMockSecondToArray = $cdfArrayMock;

    $cdfObjectMockFirstToArray['uuid'] = '55555555-0000-0000-0000-000000000000';
    $cdfObjectMockFirstToArray['origin'] = '11111111-0000-0000-0000-000000000000';

    $cdfObjectMockFirstToArray['uuid'] = '66666666-0000-0000-0000-000000000000';
    $cdfObjectMockFirstToArray['origin'] = '22222222-0000-0000-0000-000000000000';

    $cdfObjectMockFirst->expects($this->any())
      ->method('toArray')
      ->willReturn($cdfObjectMockFirstToArray);

    $cdfObjectMockSecond->expects($this->any())
      ->method('getUuid')
      ->willReturn('66666666-0000-0000-0000-000000000000');

    $cdfObjectMockSecond->expects($this->any())
      ->method('toArray')
      ->willReturn($cdfObjectMockSecondToArray);

    return [
      [
        [
          $cdfObjectMockFirst,
          $cdfObjectMockSecond,
        ],
        json_encode(['entities' => []]),
        json_encode([
          'entities' => [
            $cdfObjectMockFirstToArray,
            $cdfObjectMockSecondToArray,
          ],
        ]),
      ],
    ];
  }

}
