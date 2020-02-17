<?php

namespace Acquia\ContentHubClient\test\Event;

use Acquia\ContentHubClient\CDF\CDFObject;
use Acquia\ContentHubClient\CDF\CDFObjectInterface;
use Acquia\ContentHubClient\Event\GetCDFTypeEvent;
use PHPUnit\Framework\TestCase;

class GetCDFTypeEventTest extends TestCase {

  private const DATA = [
    'type' => 'dummy_type',
  ];

  /**
   * @var GetCDFTypeEvent
   */
  private $getCdfTypeEvent;

  private $mockedCdfObject;

  public function setUp(): void {
    parent::setUp();

    $this->mockedCdfObject = $this->getMockBuilder(CDFObject::class)
      ->disableOriginalConstructor()
      ->getMock();
    $this->getCdfTypeEvent = new GetCDFTypeEvent(self::DATA);
  }

  public function tearDown(): void {
    parent::tearDown();

    unset($this->getCdfTypeEvent);
  }

  public function testObjectCreationWithNoTypeSpecifiedWillThrowAnException(): void {
    $this->expectException(\InvalidArgumentException::class);
    new GetCDFTypeEvent([]);
  }

  public function testGetAndSetObject(): void {
    $this->assertNull($this->getCdfTypeEvent->getObject());

    $this->getCdfTypeEvent->setObject($this->mockedCdfObject);
    $cdfTypeEvent = $this->getCdfTypeEvent->getObject();

    $this->assertEquals($this->mockedCdfObject, $cdfTypeEvent);
    $this->assertInstanceOf(CDFObjectInterface::class, $cdfTypeEvent);
  }

  public function testGetData(): void {
    $this->assertEquals(self::DATA, $this->getCdfTypeEvent->getData());
  }

  public function testGetType(): void {
    $this->assertEquals(self::DATA['type'], $this->getCdfTypeEvent->getType());
  }

}
