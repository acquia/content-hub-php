<?php

namespace Acquia\ContentHubClient\test;


use Acquia\ContentHubClient\CDF\CDFObject;
use Acquia\ContentHubClient\CDFAttribute;
use Acquia\ContentHubClient\assets\CDFAttributeChild;
use PHPUnit\Framework\TestCase;

class CDFObjectTest extends TestCase
{
    /**
     * @var CDFObject
     */
    private $cdfObject;

    public function setUp() : void
    {
        parent::setUp();
        $objectParameters = $this->getObjectData();
        $this->cdfObject = new CDFObject(
            $objectParameters['type'],
            $objectParameters['uuid'],
            $objectParameters['created'],
            $objectParameters['modified'],
            $objectParameters['origin'],
            $objectParameters['metadata']
        );
    }

    public function tearDown() : void
    {
        parent::tearDown();
        unset($this->cdfObject);
    }

    /**
     * @dataProvider objectDataProvider
     * @param $objectData
     */
    public function testToArray($objectData)
    {
        $this->assertEquals($this->cdfObject->toArray(), $objectData);
    }

    /**
     * @dataProvider objectDataProvider
     * @param $settingsData
     */
    public function testGetUuid($settingsData)
    {
        $emptyObject = new CDFObject('', '', '', '', '');
        $this->assertEmpty($emptyObject->getUuid());
        $this->assertEquals($this->cdfObject->getUuid(), $settingsData['uuid']);
    }

    /**
     * @dataProvider objectDataProvider
     * @param $settingsData
     */
    public function testGetType($settingsData)
    {
        $this->assertEquals($this->cdfObject->getType(), $settingsData['type']);
        $this->assertNotEquals($this->cdfObject->getType(), 'wrong_type');
    }

    /**
     * @dataProvider objectDataProvider
     * @param $settingsData
     */
    public function testGetCreated($settingsData)
    {
        $this->assertEquals($this->cdfObject->getCreated(), $settingsData['created']);
        $this->assertNotEquals($this->cdfObject->getCreated(), 'wrong_date');
        $this->assertNotEquals($this->cdfObject->getCreated(), $settingsData['modified']);
    }

    /**
     * @dataProvider objectDataProvider
     * @param $settingsData
     */
    public function testGetModified($settingsData)
    {
        $this->assertEquals($this->cdfObject->getModified(), $settingsData['modified']);
        $this->assertNotEquals($this->cdfObject->getModified(), 'wrong_date');
        $this->assertNotEquals($this->cdfObject->getModified(), $settingsData['created']);
    }

    /**
     * @dataProvider objectDataProvider
     * @param $settingsData
     */
    public function testGetOrigin($settingsData)
    {
        $this->assertEquals($this->cdfObject->getOrigin(), $settingsData['origin']);
        $this->assertNotEquals($this->cdfObject->getModified(), '33333333-00000000-00000000-00000000');
    }

    /**
     * @dataProvider objectDataProvider
     * @param $settingsData
     */
    public function testGetMetadata($settingsData)
    {
        $this->assertEquals($this->cdfObject->getMetadata(), $settingsData['metadata']);
        $this->assertNotEquals($this->cdfObject->getMetadata(), []);
    }

    /**
     * @dataProvider objectDataProvider
     * @param $settingsData
     */
    public function testSetMetadata($settingsData)
    {
        $oldMetadata = $settingsData['metadata'];
        $newMetadata = [
            'http://new1' => '77777777-0000-0000-0000-000000000000',
            'http://new2' => '88888888-0000-0000-0000-000000000000',
        ];
        $this->assertEquals($this->cdfObject->getMetadata(), $oldMetadata);
        $this->cdfObject->setMetadata($newMetadata);
        $this->assertEquals($this->cdfObject->getMetadata(), $newMetadata);
        $this->assertNotEquals($this->cdfObject->getMetadata(), $oldMetadata);
    }

    public function testGetModuleDependencies()
    {
        $moduleValue = 'module_value';
        $this->cdfObject->setMetadata([]);
        $this->assertEquals($this->cdfObject->getModuleDependencies(), []);
        $this->cdfObject->setMetadata([
            'dependencies' => [
                'module' => $moduleValue,
            ]
        ]);
        $this->assertEquals($this->cdfObject->getModuleDependencies(), $moduleValue);
    }

    public function testGetDependencies()
    {
        $entityValue = 'entity_value';
        $this->cdfObject->setMetadata([]);
        $this->assertEquals($this->cdfObject->getDependencies(), []);
        $this->cdfObject->setMetadata([
            'dependencies' => [
                'entity' => $entityValue,
            ]
        ]);
        $this->assertEquals($this->cdfObject->getDependencies(), $entityValue);
    }

    public function testProcessedDependencies()
    {
        $this->assertFalse($this->cdfObject->hasProcessedDependencies());
        $this->cdfObject->markProcessedDependencies();
        $this->assertTrue($this->cdfObject->hasProcessedDependencies());
    }

    public function testAddIncorrectAttribute()
    {
        try {
            $dummyClassName = 'DummyClass';
            $this->cdfObject->addAttribute(
                'dummy_attribute_id',
                CDFAttribute::TYPE_ARRAY_BOOLEAN,
                [],
                CDFObject::LANGUAGE_UNDETERMINED,
                $dummyClassName);
            $this->fail(sprintf("It was expected an exception with \"%s\" class.", $dummyClassName));
        } catch (\Exception $e) {
            $this->assertEquals(sprintf("The %s class must be a subclass of \Acquia\ContentHubClient\CDFAttribute", $dummyClassName), $e->getMessage());
        }
    }

    /**
     * @dataProvider attributeDataProvider
     * @param $value
     */
    public function testAddAttribute($value)
    {
        $this->assertEquals($this->cdfObject->getAttributes(), []);

        try {
            $this->cdfObject->addAttribute('attribute_id_1', CDFAttribute::TYPE_ARRAY_INTEGER, $value, CDFObject::LANGUAGE_UNDETERMINED, CDFAttribute::class);
            $this->assertEquals(
                $this->cdfObject->getAttribute('attribute_id_1'),
                (new CDFAttribute('attribute_id_1', CDFAttribute::TYPE_ARRAY_INTEGER, $value, CDFObject::LANGUAGE_UNDETERMINED))
            );
        } catch (\Exception $exception) {
        }
    }

    /**
     * @dataProvider attributeDataProvider
     * @param $value
     */
    public function testAddAttributeWithChildClass($value)
    {
        try {
            $this->cdfObject->addAttribute('child_attribute_id_1', CDFAttribute::TYPE_ARRAY_INTEGER, $value[CDFObject::LANGUAGE_UNDETERMINED], CDFObject::LANGUAGE_UNDETERMINED, CDFAttributeChild::class);
            $this->assertEquals($this->cdfObject->getAttribute('child_attribute_id_1')->getValue(), $value[CDFObject::LANGUAGE_UNDETERMINED]);
            $this->assertEquals($this->cdfObject->getMetadata()['attributes']['child_attribute_id_1']['class'], CDFAttributeChild::class);
        } catch (\Exception $exception) {
        }
    }

    public function getObjectData()
    {
        return [
            'type' => 'product',
            'uuid' => '11111111-00000000-00000000-00000000',
            'created' => '2014-12-21T20:12:11+00:00Z',
            'modified' => '2015-12-21T20:12:11+00:00Z',
            'origin' => '22222222-0000-0000-0000-000000000000',
            'metadata' => [
                'http://example1.com/webhooks' => '00000000-0000-0000-0000-000000000000',
                'http://example2.com/webhooks' => '11111111-0000-0000-0000-000000000000',
            ],
        ];
    }

    public function attributeDataProvider()
    {
        return [
            [
                'value' => [
                    'en' => [
                        6.66,
                        3.23,
                    ],
                    'hu' => [
                        4.66,
                        4.23,
                    ],
                    CDFObject::LANGUAGE_UNDETERMINED => [
                        1.22,
                        1.11,
                    ],
                ],
            ]
        ];
    }

    public function objectDataProvider()
    {
        return [
            [
                $this->getObjectData()
            ],
        ];
    }
}
