<?php

namespace Acquia\ContentServicesClient\test;

use Acquia\ContentServicesClient\Attribute;

class AttributeTest extends \PHPUnit_Framework_TestCase
{
    private function setAttributeData()
    {
        return [
            "attributes" => [
                "title" => [
                    "type" => "string",
                    "value" => [
                        "en" => "A",
                        "hu" => "B",
                        "und" => "C",
                    ],
                ],
            ],
        ];
    }

    public function testCreateAttribute()
    {
        $data = $this->setAttributeData();
        $attribute = new Attribute();
        $attribute->setValue($data['attributes']['title']['value']);
        $attribute->setType($data['attributes']['title']['type']);
        $this->assertEquals('string', $attribute->getType());
        $this->assertEquals('A', $attribute->getValue()['en']);
        $this->assertFalse(isset($attribute->getValue()['unknown']));
    }
}
