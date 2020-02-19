<?php

namespace Acquia\ContentHubClient\test\Event;

use Acquia\ContentHubClient\CDF\CDFObject;
use Acquia\ContentHubClient\CDF\CDFObjectInterface;
use Acquia\ContentHubClient\Event\GetCDFTypeEvent;
use PHPUnit\Framework\TestCase;

/**
 * Class GetCDFTypeEventTest.
 *
 * @covers \Acquia\ContentHubClient\Event\GetCDFTypeEvent
 *
 * @package Acquia\ContentHubClient\test\Event
 */
class GetCDFTypeEventTest extends TestCase {

  /**
   * Test data.
   */
  private const DATA = [
    'type' => 'dummy_type',
  ];

  /**
   * GetCDFTypeEvent instance.
   *
   * @var \Acquia\ContentHubClient\Event\GetCDFTypeEvent
   */
  private $getCdfTypeEvent;

  /**
   * Mocked CDF object.
   *
   * @var \PHPUnit\Framework\MockObject\MockObject
   */
  private $mockedCdfObject;

  /**
   * {@inheritDoc}
   */
  public function setUp(): void {
    parent::setUp();

    $this->mockedCdfObject = $this->getMockBuilder(CDFObject::class)
      ->disableOriginalConstructor()
      ->getMock();
    $this->getCdfTypeEvent = new GetCDFTypeEvent(self::DATA);
  }

  /**
   * {@inheritDoc}
   */
  public function tearDown(): void {
    parent::tearDown();

    unset($this->getCdfTypeEvent);
  }

  /**
   * Tests event creation.
   */
  public function testObjectCreationWithNoTypeSpecifiedWillThrowAnException(): void {
    $this->expectException(\InvalidArgumentException::class);
    new GetCDFTypeEvent([]);
  }

  /**
   * Tests event getter and setter.
   */
  public function testGetAndSetObject(): void {
    $this->assertNull($this->getCdfTypeEvent->getObject());

    $this->getCdfTypeEvent->setObject($this->mockedCdfObject);
    $cdfTypeEvent = $this->getCdfTypeEvent->getObject();

    $this->assertEquals($this->mockedCdfObject, $cdfTypeEvent);
    $this->assertInstanceOf(CDFObjectInterface::class, $cdfTypeEvent);
  }

  /**
   * @covers \Acquia\ContentHubClient\Event\GetCDFTypeEvent::getData
   */
  public function testGetData(): void {
    $this->assertEquals(self::DATA, $this->getCdfTypeEvent->getData());
  }

  /**
   * @covers \Acquia\ContentHubClient\Event\GetCDFTypeEvent::getType
   */
  public function testGetType(): void {
    $this->assertEquals(self::DATA['type'], $this->getCdfTypeEvent->getType());
  }

}
