<?php

namespace Acquia\ContentHubClient\test\Data\Formatter\Localizer;

use Acquia\ContentHubClient\Data\Formatter\Localizer\Drupal7 as Localizer;

/**
 * Drupal7 data localizer test.
 *
 * @coversDefaultClass Acquia\ContentHubClient\Data\Formatter\Localizer\Drupal7
 * @group content-hub-php
 */
class Drupal7Test extends \PHPUnit_Framework_TestCase
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
        $data = ['same standardized data'];
        $localizerConfig = [
            'dataType' => 'ListEntities',
        ];
        $updatedData = $localizer->localize($data, $localizerConfig);

        $expected = ['same standardized data'];
        $this->assertEquals($expected, $updatedData);
    }

}
