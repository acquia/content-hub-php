<?php

namespace Acquia\ContentHubClient\test\EventSubscriber;

use Acquia\ContentHubClient\CDF\CDFObject;
use Acquia\ContentHubClient\CDF\CDFObjectInterface;
use Acquia\ContentHubClient\CDF\ClientCDFObject;
use Acquia\ContentHubClient\ContentHubLibraryEvents;
use Acquia\ContentHubClient\Event\GetCDFTypeEvent;
use Acquia\ContentHubClient\EventSubscriber\ClientCDF;
use PHPUnit\Framework\TestCase;

/**
 * Class ClientCDFTest.
 *
 * @covers \Acquia\ContentHubClient\EventSubscriber\ClientCDF
 *
 * @runTestsInSeparateProcesses
 * @preserveGlobalState disabled
 */
class ClientCDFTest extends TestCase {

  /**
   * ClientCDF instance.
   *
   * @var \Acquia\ContentHubClient\EventSubscriber\ClientCDF
   */
  private $client_cdf; // phpcs:ignore

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

    $this->client_cdf = new ClientCDF();
    $this->handler = current(current($this->client_cdf::getSubscribedEvents()[ContentHubLibraryEvents::GET_CDF_CLASS]));
  }

  /**
   * {@inheritdoc}
   */
  public function tearDown(): void {
    parent::tearDown();

    unset($this->client_cdf, $this->handler);
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
  public function testEventGetsHandledWhenTypeIsClient() {
    $event = $this->getGetCDFTypeEvent();
    $event->expects($this->once())
      ->method('getType')
      ->willReturn('client');

    $event->expects($this->once())
      ->method('getData');

    $event->expects($this->once())
      ->method('setObject');

    $event->expects($this->once())
      ->method('stopPropagation');

    $this->client_cdf->{$this->handler}($event);
  }

  /**
   * {@inheritdoc}
   */
  public function testHandlerDoesNothingWhenTypeIsNonClient() {
    $event = $this->getGetCDFTypeEvent();
    $event->expects($this->once())
      ->method('getType')
      ->willReturn('non-client');

    $event->expects($this->never())
      ->method('getData');

    $event->expects($this->never())
      ->method('setObject');

    $event->expects($this->never())
      ->method('stopPropagation');

    $this->client_cdf->{$this->handler}($event);
  }

  /**
   * Mock builder for GetCDFTypeEvent class.
   *
   * @return \Acquia\ContentHubClient\Event\GetCDFTypeEvent
   *   Mocked object.
   */
  private function getGetCDFTypeEvent(): GetCDFTypeEvent { // phpcs:ignore
    $mock_cdf_object = \Mockery::mock(CDFObject::class);
    $mock_client_cdf_object = \Mockery::mock('overload:' . ClientCDFObject::class);

    $mock_client_cdf_object->shouldReceive('fromArray')
      ->once()
      ->andReturn($mock_cdf_object);

    return $this->getMockBuilder(GetCDFTypeEvent::class)
      ->disableOriginalConstructor()
      ->setMethods([
        'getType',
        'setObject',
        'stopPropagation',
        'getData',
        'getObject',
      ])
      ->getMock();
  }

}
