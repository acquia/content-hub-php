<?php

namespace Acquia\ContentHubClient\test;

use Acquia\ContentHubClient\CDF\CDFObject;
use Acquia\ContentHubClient\CDFAttribute;

class AttributeTest extends \PHPUnit_Framework_TestCase
{
    private $attributeData;
    private $attributeId;

    public function setUp()
    {
        $this->attributeData = $this->getAttributeData();
        $this->attributeId = $this->attributeData['attributes']['id'];
    }

    public function tearDown()
    {
        unset($this->attributeId);
        unset($this->attributeData);
    }

    public function testCreateStringAttribute()
    {
        $title = $this->attributeData['attributes']['title'];

        $attribute = new CDFAttribute($this->attributeId, CDFAttribute::TYPE_STRING);
        $this->assertEquals('string', $attribute->getType());

        foreach ($title['value'] as $language => $value) {
            $attribute->setValue($value, $language);
        }

        $this->assertEquals($title['value'], $attribute->getValue());
    }

    public function testCreateNumericAttribute()
    {
        $numericAttribute = $this->attributeData['attributes']['num'];

        $attribute = new CDFAttribute($this->attributeId, CDFAttribute::TYPE_NUMBER);
        $this->assertEquals('number', $attribute->getType());

        foreach ($numericAttribute['value'] as $language => $value) {
            $attribute->setValue($value, $language);
        }

        $this->assertEquals($numericAttribute['value'], $attribute->getValue());
//        @todo: Ask if there should be a functionality like this:
//        $this->assertEquals($numericAttribute['value'][CDFObject::LANGUAGE_UNDETERMINED], $attribute->getValue()['it']);
//        test
    }

    public function testCreateNumericArrayAttribute()
    {
        $numericArrayAttribute = $this->attributeData['attributes']['num_array'];

        $attribute = new CDFAttribute($this->attributeId, CDFAttribute::TYPE_ARRAY_NUMBER);
        $this->assertEquals('array<number>', $attribute->getType());

        foreach ($numericArrayAttribute['value'] as $language => $value) {
            $attribute->setValue($value, $language);
        }

        $this->assertEquals($numericArrayAttribute['value'], $attribute->getValue());

//        @todo: not sure if we need this
//        $data_it = [
//            '2.34',
//            '3.23'
//        ];
//        $data['value']['it'] = [
//            2.34,
//            3.23
//        ];
//        $attribute->setValue($data_it, 'it');
//        $this->assertEquals($data['value']['it'], $attribute->getValue('it'));
//
//        unset($data['value']['it']);
//        $attribute->removeValue('it');
//        $this->assertEquals($data['value'], $attribute->getValues());
    }

    public function testUnsupportedDataTypeAttribute()
    {
        try {
            $dataType = 'unsupported_data_type';
            $attribute = new CDFAttribute($this->attributeId, $dataType);
            $this->fail(sprintf("It was expected an exception from \"%s\".", $dataType));
        } catch (\Exception $e) {
            $this->assertEquals(sprintf("Unsupported CDF Attribute data type \"%s\".", $dataType), $e->getMessage());
        }
    }

    private function getAttributeData()
    {
        return [
            "attributes" => [
                "id" => 1,
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
                        CDFObject::LANGUAGE_UNDETERMINED => 1.23
                    ]
                ],
                "title" => [
                    "type" => "string",
                    "value" => [
                        "en" => "nothing",
                        "es" => "nada",
                        CDFObject::LANGUAGE_UNDETERMINED => "niente"
                    ]
                ]
            ],
        ];
    }
}
