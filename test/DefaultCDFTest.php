<?php

namespace Acquia\ContentHubClient\test;


use Acquia\ContentHubClient\CDF\CDFObject;
use Acquia\ContentHubClient\CDF\CDFObjectInterface;
use Acquia\ContentHubClient\ContentHubLibraryEvents;
use Acquia\ContentHubClient\Event\GetCDFTypeEvent;
use Acquia\ContentHubClient\EventSubscriber\DefaultCDF;
use PHPUnit\Framework\TestCase;

/**
 * @runTestsInSeparateProcesses
 * @preserveGlobalState disabled
 */
class DefaultCDFTest extends TestCase
{
  /**
   * @var DefaultCDF
   */
  private $defaultCdf;

  /**
   *
   */
  public function setUp() : void
  {
    parent::setUp();
    $this->defaultCdf = new DefaultCDF();
  }

  /**
   *
   */
  public function tearDown() : void
  {
    parent::tearDown();
    unset($this->defaultCdf);
  }

  /**
   */
  public function testGetSubscribedEvents()
  {
    $subscribedEvents = [
      ContentHubLibraryEvents::GET_CDF_CLASS => [
        [
          'onGetCDFType',
        ],
      ],
    ];
    $this->assertEquals($subscribedEvents, $this->defaultCdf->getSubscribedEvents());
  }

  /**
   *
   */
  public function testOnGetCDFType()
  {
    $this->assertNull($this->defaultCdf->onGetCDFType($this->getGetCDFTypeEvent()));
  }

  public function getGetCDFTypeEvent()
  {
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
      ->willReturn(null);
    $getCDFTypeEventMock->expects($this->any())
      ->method('stopPropagation')
      ->willReturn(null);

    return $getCDFTypeEventMock;
  }

}
