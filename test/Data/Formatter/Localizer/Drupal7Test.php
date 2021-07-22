<?php

namespace Acquia\ContentHubClient\test\Data\Formatter\Localizer;

use Acquia\ContentHubClient\Data\Formatter\Localizer\Drupal7 as Localizer;
use PHPUnit\Framework\TestCase;

/**
 * Drupal7 data localizer test.
 *
 * @coversDefaultClass Acquia\ContentHubClient\Data\Formatter\Localizer\Drupal7
 * @group content-hub-php
 */
class Drupal7Test extends TestCase
{
    /**
     * Tests the localizeEntity() method.
     *
     * @covers ::localizeEntity
     */
    public function testLocalizeEntity()
    {
        $config = [];
        $localizer = new Localizer($config);
        $data = [
            'type' => 'taxonomy_term',
            'attributes' => [
                'boolean_attribute' => [
                    'type' => 'boolean',
                    'value' => [
                        'und' => TRUE,
                        'de' => FALSE,
                    ]
                ],
            ],
        ];
        $localizeConfig = [
            'dataType' => 'Entity',
        ];
        $updatedData = $localizer->localize($data, $localizeConfig);

        $expected = [
            'type' => 'taxonomy_term',
            'attributes' => [
                'boolean_attribute' => [
                    'type' => 'boolean',
                    'value' => [
                        'und' => TRUE,
                        'de' => NULL,
                    ],
                ],
                'parent' => [
                    'type' => 'array<reference>',
                    'value' => [
                        'und' => [],
                    ],
                ],
            ],
        ];
        $this->assertEquals($expected, $updatedData);
    }

    /**
     * Tests the localizeListEntities() method.
     *
     * @covers ::localizeListEntities
     */
    public function testLocalizeListEntities()
    {
        $config = [];
        $localizer = new Localizer($config);
        $data = [
            'data' => [
                0 => [
                    'type' => 'taxonomy_term',
                    'attributes' => [
                        'name' => [
                            'und' => 'my_taxonomy_term',
                            'en' => [
                                'my_taxonomy_term_1',
                                'my_taxonomy_term_2',
                            ],
                        ],
                    ],
                ],
            ],
        ];
        $localizerConfig = [
            'dataType' => 'ListEntities',
        ];
        $updatedData = $localizer->localize($data, $localizerConfig);

        $expected = [
            'data' => [
                0 => [
                    'type' => 'taxonomy_term',
                    'attributes' => [
                        'name' => [
                            'und' => 'my_taxonomy_term',
                            'en' => 'my_taxonomy_term_1',
                        ],
                    ],
                ],
            ],
        ];
        $this->assertEquals($expected, $updatedData);
    }

}
