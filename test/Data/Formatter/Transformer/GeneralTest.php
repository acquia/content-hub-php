<?php

namespace Acquia\ContentHubClient\test\Data\Formatter\Transformer;

use Acquia\ContentHubClient\Data\Formatter\Transformer\General as Transformer;
use PHPUnit\Framework\TestCase;

/**
 * General data transformer test.
 *
 * @coversDefaultClass Acquia\ContentHubClient\Data\Formatter\Transformer\General
 * @group content-hub-php
 */
class GeneralTest extends TestCase
{
    /**
     * Tests the rename() method, index does not exist.
     *
     * @covers ::rename
     */
    public function testRenameIndexDoesNotExist()
    {
        $transformer = new Transformer();
        $data = [
            'anotherName' => 'data',
        ];
        $transformer->rename($data, 'oldName', 'newName');

        $expected = [
            'anotherName' => 'data',
        ];
        $this->assertEquals($expected, $data);
    }

    /**
     * Tests the rename() method.
     *
     * @covers ::rename
     */
    public function testRename()
    {
        $transformer = new Transformer();
        $data = [
            'oldName' => 'data',
        ];
        $transformer->rename($data, 'oldName', 'newName');

        $expected = [
            'newName' => 'data',
        ];
        $this->assertEquals($expected, $data);
    }

    /**
     * Tests the duplicate() method, index does not exist.
     *
     * @covers ::duplicate
     */
    public function testDuplicateIndexDoesNotExist()
    {
        $transformer = new Transformer();
        $data = [
            'anotherName' => 'data',
        ];
        $transformer->duplicate($data, 'oldName', 'newName');

        $expected = [
            'anotherName' => 'data',
        ];
        $this->assertEquals($expected, $data);
    }

    /**
     * Tests the duplicate() method.
     *
     * @covers ::duplicate
     */
    public function testDuplicate()
    {
        $transformer = new Transformer();
        $data = [
            'oldName' => 'data',
        ];
        $transformer->duplicate($data, 'oldName', 'newName');

        $expected = [
            'oldName' => 'data',
            'newName' => 'data',
        ];
        $this->assertEquals($expected, $data);
    }

    /**
     * Tests the multipleToSingle() method.
     *
     * @covers ::multipleToSingle
     */
    public function testMultipleToSingle()
    {
        $transformer = new Transformer();
        $data = [
            'oldName' => [
                'und' => 'value_und',
                'en' => [
                    'my_value_1',
                    'my_value_2',
                ],
            ],
        ];
        $transformer->multipleToSingle($data, 'oldName');

        $expected = [
            'oldName' => [
                'und' => 'value_und',
                'en' =>  'my_value_1',
            ],
        ];
        $this->assertEquals($expected, $data);
    }

}
