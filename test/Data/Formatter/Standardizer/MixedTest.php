<?php

namespace Acquia\ContentHubClient\test\Data\Formatter\Standardizer;

use Acquia\ContentHubClient\Data\Formatter\Standardizer\Mixed as Standardizer;
use PHPUnit\Framework\TestCase;

/**
 * Mixed data standardizer test.
 *
 * @coversDefaultClass Acquia\ContentHubClient\Data\Formatter\Standardizer\Mixed
 * @group content-hub-php
 */
class GeneralTest extends TestCase
{
    /**
     * Tests the standardizeListEntities() method, empty data.
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
            'dataType' => 'ListEntities',
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
    public function testStandardizeListEntities()
    {
        $config = [
            'defaultLanguageId' => 'de',
        ];
        $standardizer = new Standardizer($config);
        $data = [
            'data' => [
                0 => [
                    'attributes' => [
                        'attributeName1' => [
                            'und' => 'undValue',
                        ],
                    ],
                ],
                1 => [
                    'attributes' => [
                        'attributeName1' => [
                            'de' => 'deValue',
                        ],
                    ],
                ],
                2 => [
                    'attributes' => [
                        'attributeName1' => [
                            'und' => 'undValue',
                            'de' => 'deValue',
                        ],
                    ],
                ],
            ],
        ];
        $standardizeConfig = [
            'dataType' => 'ListEntities',
        ];
        $updatedData = $standardizer->standardize($data, $standardizeConfig);

        $expected = [
            'data' => [
                0 => [
                    'attributes' => [
                        'attributeName1' => [
                            'und' => 'undValue',
                            'de' => 'undValue',
                        ],
                    ],
                ],
                1 => [
                    'attributes' => [
                        'attributeName1' => [
                            'und' => 'deValue',
                            'de' => 'deValue',
                        ],
                    ],
                ],
                2 => [
                    'attributes' => [
                        'attributeName1' => [
                            'und' => 'undValue',
                            'de' => 'deValue',
                        ],
                    ],
                ],
            ],
        ];
        $this->assertEquals($expected, $updatedData);
    }

}
