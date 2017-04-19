<?php

namespace Acquia\ContentHubClient\test\Data\Formatter\Localizer;

use Acquia\ContentHubClient\Data\Formatter\Localizer\Entity\Drupal7\File as Localizer;

/**
 * File data localizer test.
 *
 * @coversDefaultClass Acquia\ContentHubClient\Data\Formatter\Localizer\Entity\Drupal7\File
 * @group content-hub-php
 */
class FileTest extends \PHPUnit_Framework_TestCase
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
                'filemime' => [
                    'value' => [
                        'und' => [
                            'filemimeValue1a/filemimeValue1b',
                            'filemimeValue2a/filemimeValue2b',
                        ],
                        'de' => [
                            'filemimeValue3a/filemimeValue3b',
                            'filemimeValue4a/filemimeValue4b',
                        ],
                    ],
                ],
                'filename' => ['filename data'],
                'filesize' => ['filesize data'],
            ],
        ];
        $localizer->localizeEntity($data);

        $expected = [
            'attributes' => [
                'language' => 'langcode data',
                'mime' => [
                    'value' => [
                        'und' => [
                            'filemimeValue1a/filemimeValue1b',
                            'filemimeValue2a/filemimeValue2b',
                        ],
                        'de' => [
                            'filemimeValue3a/filemimeValue3b',
                            'filemimeValue4a/filemimeValue4b',
                        ],
                    ],
                ],
                'name' => ['filename data'],
                'size' => ['filesize data'],
                'type' => [
                    'type' => 'string',
                    'value' => [
                        'und' => 'filemimeValue1a',
                        'de' => 'filemimeValue3a',
                    ],
                ],
            ],
        ];
        $this->assertEquals($expected, $data);
    }

}
