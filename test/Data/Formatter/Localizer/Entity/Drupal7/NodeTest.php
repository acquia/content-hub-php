<?php

namespace Acquia\ContentHubClient\test\Data\Formatter\Localizer\Entity\Drupal7;

use Acquia\ContentHubClient\Data\Formatter\Localizer\Entity\Drupal7\Node as Localizer;
use PHPUnit\Framework\TestCase;

/**
 * Node data localizer test.
 *
 * @coversDefaultClass Acquia\ContentHubClient\Data\Formatter\Localizer\Entity\Drupal7\Node
 * @group content-hub-php
 */
class NodeTest extends TestCase
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
            ],
        ];
        $localizer->localizeEntity($data);

        $expected = [
            'attributes' => [
                'language' => 'langcode data',
            ],
        ];
        $this->assertEquals($expected, $data);
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
            'attributes' => [
                'title' => [
                    'und' => 'my_title',
                    'en' => [
                        'my_title_1',
                        'my_title_2',
                    ],
                ],
            ],
        ];
        $localizerConfig = [
            'dataType' => 'ListEntities',
        ];
        $localizer->localizeListEntities($data, $localizerConfig);

        $expected = [
            'attributes' => [
                'title' => [
                    'und' => 'my_title',
                    'en' => 'my_title_1',
                ],
            ],
        ];
        $this->assertEquals($expected, $data);
    }
}
