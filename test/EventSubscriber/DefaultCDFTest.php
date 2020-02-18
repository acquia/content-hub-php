<?php

namespace Acquia\ContentHubClient\test\EventSubscriber;

use Acquia\ContentHubClient\CDF\CDFObject;
use Acquia\ContentHubClient\CDF\CDFObjectInterface;
use Acquia\ContentHubClient\ContentHubLibraryEvents;
use Acquia\ContentHubClient\Event\GetCDFTypeEvent;
use Acquia\ContentHubClient\EventSubscriber\DefaultCDF;
use PHPUnit\Framework\TestCase;

/**
 * @covers \Acquia\ContentHubClient\EventSubscriber\DefaultCDF
 *
 * @runTestsInSeparateProcesses
 * @preserveGlobalState disabled
 */
class DefaultCDFTest extends TestCase {

  /**
   * DefaultCDF instance.
   *
   * @var \Acquia\ContentHubClient\EventSubscriber\DefaultCDF
   */
  private $defaultCdf;

  /**
   * Event handler.
   *
   * @var mixed
   */
  private $handler;

  /**
   * {@inheritdoc}
   */
  public function setUp(): void {
    parent::setUp();

    $this->defaultCdf = new DefaultCDF();
    $this->handler = current(current($this->defaultCdf::getSubscribedEvents()[ContentHubLibraryEvents::GET_CDF_CLASS]));
  }

  /**
   * {@inheritdoc}
   */
  public function tearDown(): void {
    parent::tearDown();

    unset($this->clientCdf, $this->handler);
  }

  /**
   * {@inheritdoc}
   */
  public function testGetSubscribedEventsAddsHandlerToEvent() {
    $this->assertNotNull($this->handler);
  }

  /**
   * {@inheritdoc}
   */
  public function testHandler() {
    $cdfObjectInterfaceMock = \Mockery::mock(CDFObjectInterface::class);
    $cdfObjectMock = \Mockery::mock('overload:' . CDFObject::class);

    $cdfObjectMock->shouldReceive('fromArray')
      ->once()
      ->andReturn($cdfObjectInterfaceMock);

    $getCDFTypeEventMock = $this->getMockBuilder(GetCDFTypeEvent::class)
      ->disableOriginalConstructor()
      ->setMethods(['setObject', 'stopPropagation', 'getData'])
      ->getMock();

    $getCDFTypeEventMock->expects($this->once())
      ->method('getData');

    $getCDFTypeEventMock->expects($this->once())
      ->method('setObject');

    $getCDFTypeEventMock->expects($this->once())
      ->method('stopPropagation');

    $this->defaultCdf->{$this->handler}($getCDFTypeEventMock);
  }

}
