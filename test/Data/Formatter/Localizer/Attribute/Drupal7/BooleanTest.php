<?php

namespace Acquia\ContentHubClient\test\Data\Formatter\Localizer\Attribute\Drupal7;

use Acquia\ContentHubClient\Data\Formatter\Localizer\Attribute\Drupal7\Boolean as Localizer;
use PHPUnit\Framework\TestCase;

/**
 * Boolean data localizer test.
 *
 * @coversDefaultClass Acquia\ContentHubClient\Data\Formatter\Localizer\Attribute\Drupal7\Boolean
 * @group content-hub-php
 */
class BooleanTest extends TestCase
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
                'und' => TRUE,
                'de' => FALSE,
            ],
        ];
        $localizer->localizeEntity($data);

        $expected = [
            'value' => [
                'und' => TRUE,
                'de' => NULL,
            ],
        ];
        $this->assertEquals($expected, $data);
        // AssertEquals uses "==", so there is an added AssertNull here.
        $this->assertNull($data['value']['de']);
    }

}
