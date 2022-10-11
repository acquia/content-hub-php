<?php

namespace Acquia\ContentHubClient\test\Data;

use Acquia\ContentHubClient\Data\Adapter;
use PHPUnit\Framework\TestCase;

/**
 * Adapter test.
 *
 * @coversDefaultClass Acquia\ContentHubClient\Data\Adapter
 * @group content-hub-php
 */
class AdapterTest extends TestCase
{
    /**
     * Tests the constructor() method, adapter schema is unsupported.
     *
     * @covers ::__construct
     */
    public function testTranslateAdapterSchemaUnsupported()
    {
        $adapterConfig = [
            'schemaId' => 'NonExistentSchema',
        ];
        $this->expectException('\Acquia\ContentHubClient\Data\Exception\UnsupportedFormatException');
        $this->expectExceptionCode(0);
        $this->expectExceptionMessage('The localized data schema is not yet supported: NonExistentSchema');
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
        $this->expectException('\Acquia\ContentHubClient\Data\Exception\UnsupportedFormatException');
        $this->expectExceptionCode(0);
        $this->expectExceptionMessage('This data formatting action is not yet supported: Standardizer\NonExistentSchema');
        $translatedData = $adapter->translate($data, []);

        $expected = ['should not reach here'];
        $this->assertEquals($expected, $translatedData);
    }

    /**
     * Tests translate() method, adapter schema is undeterminable.
     *
     * @covers ::translate
     */
    public function testTranslateDataSchemaUndeterminable()
    {
        $adapterConfig = [
            'schemaId' => 'Drupal7',
        ];
        $adapter = new Adapter($adapterConfig);
        $data = ['no translation due to undeterminable schema id'];
        $translatedData = $adapter->translate($data, []);

        $expected = ['no translation due to undeterminable schema id'];
        $this->assertEquals($expected, $translatedData);
    }

    /**
     * Tests translate() method, both adapter and data schema are "Drupal7".
     *
     * @covers ::translate
     */
    public function testTranslateDataBothSchemaDrupal7()
    {
        $adapterConfig = [
            'schemaId' => 'Drupal7',
        ];
        $adapter = new Adapter($adapterConfig);
        $data = [
            'attributes' => [
                'language' => [
                    'value' => 'en',
                ],
            ],
        ];
        $translatedData = $adapter->translate($data, []);

        $expected = [
            'attributes' => [
                'language' => [
                    'value' => 'en',
                ],
            ],
        ];
        $this->assertEquals($expected, $translatedData);
    }

    /**
     * Tests translate() method, both adapter and data schema are "Drupal8".
     *
     * @covers ::translate
     */
    public function testTranslateDataBothSchemaDrupal8()
    {
        $adapterConfig = [
            'schemaId' => 'Drupal8',
        ];
        $adapter = new Adapter($adapterConfig);
        $data = [
            'attributes' => [
                'langcode' => [
                    'value' => 'en',
                ],
            ],
        ];
        $translatedData = $adapter->translate($data, []);

        $expected = [
            'attributes' => [
                'langcode' => [
                    'value' => 'en',
                ],
            ],
        ];
        $this->assertEquals($expected, $translatedData);
    }

    /**
     * Tests translate() method, do call standardizer and localizer.
     *
     * @covers ::translate
     */
    public function testTranslateDataDoCallStandardizerAndLocalizer()
    {
        $adapterConfig = [
            'schemaId' => 'Drupal7',
            'defaultLanguageId' => 'de',
        ];
        $adapter = new Adapter($adapterConfig);
        $data = [];
        $data['data'][0]['attributes']['dataIndex1']['de'] = 'dataValue1';
        $translateConfig = [
            'dataType' => 'ListEntities',
        ];
        $translatedData = $adapter->translate($data, $translateConfig);

        $expected = [];
        $expected['data'][0]['attributes']['dataIndex1']['de'] = 'dataValue1';
        $expected['data'][0]['attributes']['dataIndex1']['und'] = 'dataValue1';
        $this->assertEquals($expected, $translatedData);
    }

}
