<?php

namespace Acquia\ContentHubClient\test\Data\Formatter\Standardizer;

use Acquia\ContentHubClient\Data\Formatter\Standardizer\Drupal8 as Standardizer;

/**
 * Drupal8 data standardizer test.
 *
 * @coversDefaultClass Acquia\ContentHubClient\Data\Formatter\Standardizer\Drupal8
 * @group content-hub-php
 */
class Drupal8Test extends \PHPUnit_Framework_TestCase
{
    /**
     * Tests the standardizeEntities() method, empty data.
     *
     * @covers ::standardizeListEntities
     */
    public function testStandardizeListEntitiesEmptyData()
    {
        $config = [
            'defaultLanguageId' => 'de',
        ];
        $standardizer = new Standardizer($config);
        $data = [];
        $standardizeConfig = [
            'dataType' => 'Entity',
        ];
        $standardizer->standardize($data, $standardizeConfig);

        $expected = [];
        $this->assertEquals($expected, $data);
    }

    /**
     * Tests the standardizeListEntities() method.
     *
     * @covers ::standardizeListEntities
     */
    public function testRenameIndexDoesNotExist()
    {
        $config = [
            'defaultLanguageId' => 'de',
        ];
        $standardizer = new Standardizer($config);
        $data = [
            'attributes' => [
                'attributeName1' => [
                    'value' => [
                        'und' => 'undValue',
                    ],
                ],
                'attributeName2' => [
                    'value' => [
                        'de' => 'deValue',
                    ],
                ],
                'attributeName3' => [
                    'value' => [
                        'und' => 'undValue',
                        'de' => 'deValue',
                    ],
                ],
                'langcode' => [
                    'value' => [
                        'de' => 'deValue',
                    ],
                ],
                'language' => [
                    'value' => [
                        'und' => 'undValue',
                    ],
                ],
            ],
        ];
        $standardizeConfig = [
            'dataType' => 'Entity',
        ];
        $updatedData = $standardizer->standardize($data, $standardizeConfig);

        $expected = [
            'attributes' => [
                'attributeName1' => [
                    'value' => [
                        'und' => 'undValue',
                        'de' => 'undValue',
                    ],
                ],
                'attributeName2' => [
                    'value' => [
                        'de' => 'deValue',
                        'und' => 'deValue',
                    ],
                ],
                'attributeName3' => [
                    'value' => [
                        'und' => 'undValue',
                        'de' => 'deValue',
                    ],
                ],
                'langcode' => [
                    'value' => [
                        'de' => 'deValue',
                        'und' => 'und',
                    ],
                ],
                'language' => [
                    'value' => [
                        'und' => 'undValue',
                        'de' => 'de',
                    ],
                ],
            ],
        ];
        $this->assertEquals($expected, $updatedData);
    }

}
