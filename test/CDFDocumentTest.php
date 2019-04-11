<?php

namespace Acquia\ContentHubClient\test;

use Acquia\ContentHubClient\CDF\CDFObject;
use Acquia\ContentHubClient\CDFDocument;
use PHPUnit\Framework\TestCase;

class CDFDocumentTest extends TestCase
{
  /**
   * @var CDFDocument
   */
  protected $cdfDocument;

  /**
   *
   */
  public function setUp() : void
  {
    parent::setUp();
    $this->cdfDocument = new CDFDocument();
  }

  /**
   *
   */
  public function tearDown() : void
  {
    parent::tearDown();
    unset($this->cdfDocument);
  }

  /**
   * @dataProvider providerEntityOperations
   * @param $objectToAdd
   * @param $notAddedObject
   */
  public function testHasEntities($objectToAdd, $notAddedObject)
  {
    //Check hasEntities method before and after we add an Object
    $this->assertEquals($this->cdfDocument->hasEntities(), false);
    $this->cdfDocument->addCdfEntity($objectToAdd);
    $this->assertEquals($this->cdfDocument->hasEntities(), true);
  }

  /**
   * @dataProvider providerEntityOperations
   * @param $objectToAdd
   * @param $notAddedObject
   */
  public function testGetEntity($objectToAdd, $notAddedObject)
  {
    //Check getting added and not added Object
    $this->cdfDocument->addCdfEntity($objectToAdd);
    $this->assertEquals($this->cdfDocument->getCdfEntity($objectToAdd->getUuid()), $objectToAdd);
    $this->assertEquals($this->cdfDocument->getCdfEntity($notAddedObject->getUuid()), null);
  }

  /**
   * @dataProvider providerEntityOperations
   * @param $objectOne
   * @param $objectTwo
   */
  public function testGetEntities($objectOne, $objectTwo)
  {
    //Check getting added and not added Object
    $this->cdfDocument->setCdfEntities($objectOne, $objectTwo);

    foreach ($this->cdfDocument->getEntities() as $entity) {
      $this->assertInstanceOf(CDFObject::class, $entity);
    }
  }

  /**
   * @dataProvider providerEntityOperations
   * @param $objectToAdd
   * @param $notAddedObject
   */
  public function testAddEntity($objectToAdd, $notAddedObject)
  {
    //Check if hasEntity will return correct values for added and not added Objects
    $this->cdfDocument->addCdfEntity($objectToAdd);
    $this->assertEquals($this->cdfDocument->hasEntity($objectToAdd->getUuid()), true);
    $this->assertEquals($this->cdfDocument->hasEntity($notAddedObject->getUuid()), false);
  }

  /**
   * @dataProvider providerEntityOperations
   * @param $objectToAdd
   */
  public function testRemoveEntity($objectToAdd)
  {
    //Test removing Entity
    $this->cdfDocument->addCdfEntity($objectToAdd);
    $this->assertEquals($this->cdfDocument->hasEntity($objectToAdd->getUuid()), true);
    $this->cdfDocument->removeCdfEntity($objectToAdd->getUuid());
    $this->assertEquals($this->cdfDocument->hasEntity($objectToAdd->getUuid()), false);
  }

  /**
   * @dataProvider providerMergeDocuments
   * @param $setOne
   * @param $setTwo
   */
  public function testMergeDocuments($setOne, $setTwo)
  {
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
   * @dataProvider providerMergeDocuments
   * @param $setOne
   * @param $setTwo
   */
  public function testMergeDocumentsByKeys($setOne, $setTwo)
  {
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
   * @dataProvider providerMergeDocumentsNoOverlap
   * @param $setOne
   * @param $setTwo
   * @param $elementFromSetTwo
   */
  public function testMergeDocumentsNoOverlap($setOne, $setTwo, $elementFromSetTwo)
  {
    $this->cdfDocument->setCdfEntities(...$setOne);
    $documentToMerge = new CDFDocument(...$setTwo);

    $this->assertFalse($this->cdfDocument->hasEntity($elementFromSetTwo->getUuid()));
    $this->cdfDocument->mergeDocuments($documentToMerge);
    $this->assertTrue($this->cdfDocument->hasEntity($elementFromSetTwo->getUuid()));
  }

  /**
   * @dataProvider providerMergeDocumentsOverlap
   * @param $setOne
   * @param $setTwo
   * @param $overlappingElement
   */
  public function testMergeDocumentsOverlap($setOne, $setTwo, $overlappingElement)
  {
    $this->cdfDocument->setCdfEntities(...$setOne);
    $documentToMerge = new CDFDocument(...$setTwo);

    $this->assertTrue($this->cdfDocument->hasEntity($overlappingElement->getUuid()));
    $this->cdfDocument->mergeDocuments($documentToMerge);
    $this->assertTrue($this->cdfDocument->hasEntity($overlappingElement->getUuid()));
  }

  /**
   * @dataProvider providerToString
   * @param $objectsList
   * @param $emptyObjectsJson
   * @param $filledObjectsJson
   */
  public function testToString($objectsList, $emptyObjectsJson, $filledObjectsJson)
  {
    $this->assertJsonStringEqualsJsonString($this->cdfDocument->toString(), $emptyObjectsJson);
    $this->cdfDocument->setCdfEntities(...$objectsList);
    $this->assertJsonStringEqualsJsonString($this->cdfDocument->toString(), $filledObjectsJson);
  }

  public function providerEntityOperations()
  {
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
        $cdfObjectMockSecond
      ]
    ];
  }

  public function providerMergeDocuments()
  {
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
          $cdfObjectMockSecond
        ]
      ])
    ];
  }

  public function providerMergeDocumentsNoOverlap()
  {
    //First set of objects
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

    //Second set of objects
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

  public function providerMergeDocumentsOverlap()
  {
    //First set of objects
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

    //Second set of objects
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

  public function providerToString()
  {
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
          $cdfObjectMockSecond
        ],
        json_encode(['entities' => []]),
        json_encode(['entities' => [
          $cdfObjectMockFirstToArray,
          $cdfObjectMockSecondToArray
        ]]),
      ]
    ];
  }

}