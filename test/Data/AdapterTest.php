<?php

namespace Acquia\ContentHubClient\test;

use Acquia\ContentHubClient\Data\Adapter;

class AdapterTest extends \PHPUnit_Framework_TestCase
{
    public function testTranslateSchemaNone()
    {
        $adapter = new Adapter();
        $translatedData = $adapter->translate(['untranslatable data'], []);
        $this->assertEquals(['untranslatable data'], $translatedData);
    }

}
