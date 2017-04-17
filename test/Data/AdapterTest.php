<?php

namespace Acquia\ContentHubClient\test;

use Acquia\ContentHubClient\Data\Adapter;

class AdapterTest extends \PHPUnit_Framework_TestCase
{
    /**
     * Tests the constructor() method, adapter schema is unsupported.
     *
     * @covers ::__construct
     *
     * @expectedException \Acquia\ContentHubClient\Data\Exception\UnsupportedFormatException
     * @expectedExceptionCode 0
     * @expectedExceptionMessage The localized data schema is not yet supported: NonExistentSchema
     */
    public function testTranslateAdapterSchemaUnsupported()
    {
        $adapterConfig = [
            'schemaId' => 'NonExistentSchema',
        ];
        new Adapter($adapterConfig);
    }

    /**
     * Tests translate() method, adapter schema is none.
     *
     * @covers ::translate
     */
    public function testTranslateAdapterSchemaNone()
    {
        $adapter = new Adapter();
        $data = ['untranslatable data'];
        $translatedData = $adapter->translate($data, []);

        $expected = ['untranslatable data'];
        $this->assertEquals($expected, $translatedData);
    }

    /**
     * Tests translate() method, adapter schema is unsupported.
     *
     * @covers ::translate
     *
     * @expectedException \Acquia\ContentHubClient\Data\Exception\UnsupportedFormatException
     * @expectedExceptionCode 0
     * @expectedExceptionMessage This data formatting action is not yet supported: Standardizer\NonExistentSchema
     */
    public function testTranslateDataSchemaUnsupported()
    {
        $adapterConfig = [
            'schemaId' => 'Drupal7',
        ];
        $adapter = new Adapter($adapterConfig);
        $data = [
            'metadata' => [
                'schema' => 'NonExistentSchema',
            ],
        ];
        $translatedData = $adapter->translate($data, []);

        $expected = ['should not reach here'];
        $this->assertEquals($expected, $translatedData);
    }

    /**
     * Tests translate() method, adapter schema is undeterminable.
     *
     * @covers ::translate
     *
     * @expectedException \Acquia\ContentHubClient\Data\Exception\DataAdapterException
     * @expectedExceptionCode 0
     * @expectedExceptionMessage The data adapter could not determine the data's schema ID.
     */
    public function testTranslateDataSchemaUndeterminable()
    {
        $adapterConfig = [
            'schemaId' => 'Drupal7',
        ];
        $adapter = new Adapter($adapterConfig);
        $data = ['Undeterminable schema id'];
        $translatedData = $adapter->translate($data, []);

        $expected = ['should not reach here'];
        $this->assertEquals($expected, $translatedData);
    }

}
