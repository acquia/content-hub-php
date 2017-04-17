<?php

namespace Acquia\ContentHubClient\test\Data\Formatter\Transformer\Entity;

use Acquia\ContentHubClient\Data\Formatter\Transformer\Entity\Drupal7 as Transformer;

/**
 * Drupal7 data transformer test.
 *
 * @coversDefaultClass Acquia\ContentHubClient\Data\Formatter\Transformer\Entity\Drupal
 * @group content-hub-php
 */
class Drupal7Test extends \PHPUnit_Framework_TestCase
{
    /**
     * Tests the arrayStringToString() method, index does not exist.
     *
     * @covers ::arrayStringToString
     */
    public function testArrayStringToString()
    {
        $transformer = new Transformer();
        $data = [
            'anotherIndex' => 'data',
        ];
        $transformer->arrayStringToString($data, 'NonExistIndex');

        $expected = [
            'anotherIndex' => 'data',
        ];
        $this->assertEquals($expected, $data);
    }

}
