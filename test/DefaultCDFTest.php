<?php

namespace Acquia\ContentHubClient\test;

use Acquia\ContentHubClient\CDF\CDFObject;
use Acquia\ContentHubClient\CDF\CDFObjectInterface;
use Acquia\ContentHubClient\ContentHubLibraryEvents;
use Acquia\ContentHubClient\Event\GetCDFTypeEvent;
use Acquia\ContentHubClient\EventSubscriber\DefaultCDF;
use PHPUnit\Framework\TestCase;

/**
 * Class DefaultCDFTest.
 *
 * @package Acquia\ContentHubClient\test
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
   * {@inheritdoc}
   */
  public function setUp(): void {
    parent::setUp();
    $this->defaultCdf = new DefaultCDF();
  }

  /**
   * {@inheritdoc}
   */
  public function tearDown(): void {
    parent::tearDown();
    unset($this->defaultCdf);
  }

  /**
   * {@inheritdoc}
   */
  public function testGetSubscribedEvents() {
    $subscribedEvents = [
      ContentHubLibraryEvents::GET_CDF_CLASS => [
        [
          'onGetCDFType',
        ],
      ],
    ];
    $this->assertEquals($subscribedEvents,
      $this->defaultCdf->getSubscribedEvents());
  }

  /**
   * {@inheritdoc}
   */
  public function testOnGetCDFType() { // phpcs:ignore
    $this->assertNull($this->defaultCdf->onGetCDFType($this->getGetCDFTypeEvent()));
  }

  /**
   * Returns GetCDFTypeEvent mock.
   *
   * @return mixed
   *   GetCDFTypeEvent mock.
   */
  public function getGetCDFTypeEvent() { // phpcs:ignore
    $cdfObjectInterfaceMock = \Mockery::mock(CDFObjectInterface::class);
    $cdfObjectMock = \Mockery::mock('overload:' . CDFObject::class);

    $cdfObjectMock->shouldReceive('fromArray')
      ->once()
      ->andReturn($cdfObjectInterfaceMock);

    $getCDFTypeEventMock = $this->getMockBuilder(GetCDFTypeEvent::class)
      ->disableOriginalConstructor()
      ->setMethods(['setObject', 'stopPropagation', 'getData'])
      ->getMock();

    $getCDFTypeEventMock->expects($this->any())
      ->method('getData')
      ->willReturn([]);
    $getCDFTypeEventMock->expects($this->any())
      ->method('setObject')
      ->willReturn(NULL);
    $getCDFTypeEventMock->expects($this->any())
      ->method('stopPropagation')
      ->willReturn(NULL);

    return $getCDFTypeEventMock;
  }

}
