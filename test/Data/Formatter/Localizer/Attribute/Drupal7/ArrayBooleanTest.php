<?php

namespace Acquia\ContentHubClient\test\Data\Formatter\Localizer\Attribute\Drupal7;

use Acquia\ContentHubClient\Data\Formatter\Localizer\Attribute\Drupal7\ArrayBoolean as Localizer;
use PHPUnit\Framework\TestCase;

/**
 * Array boolean data localizer test.
 *
 * @coversDefaultClass Acquia\ContentHubClient\Data\Formatter\Localizer\Attribute\Drupal7\ArrayBoolean
 * @group content-hub-php
 */
class ArrayBooleanTest extends TestCase
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
            'value' => [
                'und' => [
                    TRUE,
                    FALSE,
                ],
                'de' => [
                    FALSE,
                    TRUE,
                ],
            ],
        ];
        $localizer->localizeEntity($data);

        $expected = [
            'value' => [
                'und' => [
                    TRUE,
                    NULL,
                ],
                'de' => [
                    NULL,
                    TRUE,
                ],
            ],
        ];
        $this->assertEquals($expected, $data);
        // AssertEquals uses "==", so there is an added AssertNull here.
        $this->assertNull($data['value']['und'][1]);
        $this->assertNull($data['value']['de'][0]);
    }

}
