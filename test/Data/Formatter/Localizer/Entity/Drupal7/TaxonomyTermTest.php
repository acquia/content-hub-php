<?php

namespace Acquia\ContentHubClient\test\Data\Formatter\Localizer\Entity\Drupal7;

use Acquia\ContentHubClient\Data\Formatter\Localizer\Entity\Drupal7\TaxonomyTerm as Localizer;

/**
 * Taxonomy term data localizer test.
 *
 * @coversDefaultClass Acquia\ContentHubClient\Data\Formatter\Localizer\Entity\Drupal7\TaxonomyTerm
 * @group content-hub-php
 */
class TaxonomyTermTest extends \PHPUnit_Framework_TestCase
{
    /**
     * Tests the localizeEntity() method.
     *
     * @covers ::localizeEntity
     */
    public function testLocalizeEntity()
    {
        $localizer = new Localizer();
        $data = [
            'attributes' => [
                'langcode' => 'langcode data',
                'vocabulary' => ['vocabulary data'],
                'name' => [
                    'type' => 'old_type',
                    'value' => [
                        'und' => [
                            'data1',
                            'data2',
                        ],
                    ],
                ],
                'weight' => [
                    'type' => 'old_type',
                    'value' => [
                        'und' => [
                            'data1',
                            'data2',
                        ],
                    ],
                ],
                'description' => [
                    'type' => 'old_type',
                    'value' => [
                        'und' => [
                            'data1',
                            'data2',
                        ],
                    ],
                ],
            ],
        ];
        $localizer->localizeEntity($data);

        $expected = [
            'attributes' => [
                'language' => 'langcode data',
                'vocabulary' => ['vocabulary data'],
                'name' => [
                    'type' => 'string',
                    'value' => [
                        'und' => 'data1',
                    ],
                ],
                'weight' => [
                    'type' => 'string',
                    'value' => [
                        'und' => 'data1',
                    ],
                ],
                'description' => [
                    'type' => 'string',
                    'value' => [
                        'und' => 'data1',
                    ],
                ],
                'parent' => [
                    'type' => 'array<reference>',
                    'value' => [
                        'und' => [],
                    ],
                ],
                'type' => ['vocabulary data'],
            ],
        ];
        $this->assertEquals($expected, $data);
    }

}
