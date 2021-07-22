<?php

namespace Acquia\ContentHubClient\test\Data\Formatter\Localizer;

use Acquia\ContentHubClient\Data\Formatter\Localizer\Drupal8 as Localizer;
use PHPUnit\Framework\TestCase;

/**
 * Drupal8 data localizer test.
 *
 * @coversDefaultClass Acquia\ContentHubClient\Data\Formatter\Localizer\Drupal8
 * @group content-hub-php
 */
class Drupal8Test extends TestCase
{
    /**
     * Tests the localizeListEntities() method.
     *
     * @covers ::localizeListEntities
     */
    public function testLocalizeListEntities()
    {
        $config = [];
        $localizer = new Localizer($config);
        $data = ['same localized data'];
        $localizeConfig = [
            'dataType' => 'ListEntities',
        ];
        $localizer->localize($data, $localizeConfig);

        $expected = ['same localized data'];
        $this->assertEquals($expected, $data);
    }

}
