<?php

namespace Acquia\ContentHubClient\test\Data\Formatter\Transformer\Entity;

use Acquia\ContentHubClient\Data\Formatter\Transformer\Entity\Drupal7 as Transformer;
use PHPUnit\Framework\TestCase;

/**
 * Drupal7 data transformer test.
 *
 * @coversDefaultClass Acquia\ContentHubClient\Data\Formatter\Transformer\Entity\Drupal7
 * @group content-hub-php
 */
class Drupal7Test extends TestCase
{
    /**
     * Tests the arrayStringToString() method, index does not exist.
     *
     * @covers ::arrayStringToString
     */
    public function testArrayStringToStringIndexDoesNotExist()
    {
        $transformer = new Transformer();
        $data = [
            'anotherIndex' => [],
        ];
        $transformer->arrayStringToString($data, 'NonExistIndex');

        $expected = [
            'anotherIndex' => [],
        ];
        $this->assertEquals($expected, $data);
    }

    /**
     * Tests the arrayStringToString() method, element is not array.
     *
     * @covers ::arrayStringToString
     */
    public function testArrayStringToStringElementNotArray()
    {
        $transformer = new Transformer();
        $data = [
            'index1' => 'not array',
        ];
        $transformer->arrayStringToString($data, 'index1');

        $expected = [
            'index1' => 'not array',
        ];
        $this->assertEquals($expected, $data);
    }

    /**
     * Tests the arrayStringToString() method.
     *
     * @covers ::arrayStringToString
     */
    public function testArrayStringToString()
    {
        $transformer = new Transformer();
        $data = [
            'index1' => [
                'type' => 'oldType',
                'value' => [
                    'en' => [
                        'value1',
                        'value2',
                    ],
                ],
            ],
        ];
        $transformer->arrayStringToString($data, 'index1');

        $expected = [
            'index1' => [
                'type' => 'string',
                'value' => [
                    'en' => 'value1',
                ],
            ],
        ];
        $this->assertEquals($expected, $data);
    }

    /**
     * Tests the addArrayReferenceIfNotExist() method.
     *
     * @covers ::addArrayReferenceIfNotExist
     */
    public function testAddArrayReferenceIfNotExist()
    {
        $transformer = new Transformer();
        $data = [];
        $transformer->addArrayReferenceIfNotExist($data, 'index1');

        $expected = [
            'index1' => [
                'type' => 'array<reference>',
                'value' => [
                    'und' => [],
                ],
            ],
        ];
        $this->assertEquals($expected, $data);
    }

}
