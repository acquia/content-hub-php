<?php

namespace Acquia\ContentHubClient\test\Data\Formatter\Localizer;

use Acquia\ContentHubClient\Data\Formatter\Localizer\Drupal8 as Localizer;

/**
 * Drupal8 data localizer test.
 *
 * @coversDefaultClass Acquia\ContentHubClient\Data\Formatter\Localizer\Drupal8
 * @group content-hub-php
 */
class Drupal8Test extends \PHPUnit_Framework_TestCase
{
    /**
     * Tests the localizeListEntities() method.
     *
     * @covers ::localizeListEntities
     */
    public function testLocalizeListEntitiesEmptyData()
    {
        $config = [];
        $standardizer = new Localizer($config);
        $data = ['same standardized data'];
        $standardizeConfig = [
            'dataType' => 'ListEntities',
        ];
        $standardizer->localize($data, $standardizeConfig);

        $expected = ['same standardized data'];
        $this->assertEquals($expected, $data);
    }

}
