<?php

namespace Acquia\ContentHubClient\test;

use Acquia\ContentHubClient\Asset;
use PHPUnit\Framework\TestCase;

/**
 * Class AssetTest.
 *
 * @package Acquia\ContentHubClient\test
 */
class AssetTest extends TestCase {

  /**
   * @covers \Acquia\ContentHubClient\Asset
   */
  public function testCreateAsset() {
    $url = 'http://acquia.com/sites/default/files/foo.png';
    $replaceToken = '[acquia-logo]';
    $asset = new Asset();
    $asset->setUrl($url);
    $asset->setReplaceToken($replaceToken);
    $this->assertEquals($url, $asset->getUrl());
    $this->assertEquals($replaceToken, $asset->getReplaceToken());
  }

}
