<?php

namespace Acquia\ContentHubClient\test;

use Acquia\ContentHubClient\Attribute;

class AttributeTest extends \PHPUnit_Framework_TestCase
{
    private function setAttributeData()
    {
        return [
            "attributes" => [
                "num_array" => [
                    "type" => "array<number>",
                    "value" => [
                        "en" => [
                            6.66,
                            3.23
                        ],
                        "hu" => [
                            4.66,
                            4.23
                        ],
                        "und" => [
                            1.22,
                            1.11
                        ],
                    ],
                ],
                "num" => [
                    "type" => "number",
                    "value" => [
                        'en' => 13.45,
                        'es' => 1.43,
                        'und' => 1.23
                    ]
                ],
                "title" => [
                    "type" => "string",
                    "value" => [
                      "en" => "nothing",
                      "es" => "nada",
                      "und" => "niente"
                    ]
                ]
            ],
        ];
    }

    public function testCreateAttribute()
    {
        $data = $this->setAttributeData()['attributes']['title'];

        // Testing attribute with default type = string.
        $attribute = new Attribute(Attribute::TYPE_STRING);
        $this->assertEquals('string', $attribute->getType());
        $attribute->setValues($data['value']);
        $this->assertEquals($data['value']['en'], $attribute->getValue('en'));
        $this->assertEquals($data['value']['es'], $attribute->getValue('es'));
        $this->assertEquals($data['value']['und'], $attribute->getValue());

        // Testing type 'number'
        $data = $this->setAttributeData()['attributes']['num'];
        $attribute = new Attribute(Attribute::TYPE_NUMBER);
        $this->assertEquals('number', $attribute->getType());
        $attribute->setValue($data['value']['en'], 'en');
        $this->assertEquals($data['value']['en'], $attribute->getValue('en'));
        $attribute->setValue((string) $data['value']['es'], 'es');
        $this->assertEquals($data['value']['es'], $attribute->getValue('es'));
        $attribute->setValue((string) $data['value']['und']);
        $this->assertEquals($data['value']['und'], $attribute->getValue());
        $this->assertEquals($data['value']['und'], $attribute->getValue('it'));
        $this->assertEquals($data['value'], $attribute->getValues());

        // Testing 'array<number>'
        $data = $this->setAttributeData()['attributes']['num_array'];
        $attribute = new Attribute(Attribute::TYPE_ARRAY_NUMBER);
        $this->assertEquals('array<number>', $attribute->getType());
        $attribute->setValues($data['value']);
        $this->assertEquals($data['value']['en'], $attribute->getValue('en'));
        $this->assertEquals($data['value']['hu'], $attribute->getValue('hu'));
        $this->assertEquals($data['value']['und'], $attribute->getValue());
        $data_it = [
            '2.34',
            '3.23'
        ];
        $data['value']['it'] = [
            2.34,
            3.23
        ];
        $attribute->setValue($data_it, 'it');
        $this->assertEquals($data['value']['it'], $attribute->getValue('it'));

        unset($data['value']['it']);
        $attribute->removeValue('it');
        $this->assertEquals($data['value'], $attribute->getValues());

        // Test an unhandled type.
        try {
            $attribute = new Attribute('float');
            $this->fail('It was expected an exception from "float" type.');
        } catch (\Exception $e) {
            $this->assertEquals('Type handler not registered for this type: float', $e->getMessage());
        }

    }
}
