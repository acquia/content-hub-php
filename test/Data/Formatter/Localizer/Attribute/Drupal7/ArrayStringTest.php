<?php

namespace Acquia\ContentHubClient\test\Data\Formatter\Localizer\Attribute\Drupal7;

use Acquia\ContentHubClient\Data\Formatter\Localizer\Attribute\Drupal7\ArrayString as Localizer;
use PHPUnit\Framework\TestCase;

/**
 * Array string data localizer test.
 *
 * @coversDefaultClass Acquia\ContentHubClient\Data\Formatter\Localizer\Attribute\Drupal7\ArrayString
 * @group content-hub-php
 */
class ArrayStringTest extends TestCase
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
                    'single_value_field' => 'single_value_data',
                    'one_address_field' => '{"country_code":"country_code_data","address_line1":"address_line1_data","address_line2":"address_line2_data","organization":"organization_data","given_name":"given_name_data","family_name":"family_name_data"}',
                    'internal_link_field' => '{"uri":"internal:\/my_internal_uri"}',
                    'external_link_field1' => '{"uri":"my_internal_uri"}',
                    'external_link_field2' => '{"uri":"my_interinternal:\/nal_uri"}',
                ],
                'de' => [
                    'single_value_field' => 'single_value_data',
                    'restricted_html_format_field' => '{"format":"restricted_html","other_stuff":"other data"}',
                    'basic_html_format_field' => '{"format":"basic_html","other_stuff":"other data"}',
                    'full_html_format_field' => '{"format":"full_html","other_stuff":"other data"}',
                    'rich_text_format_field' => '{"format":"rich_text","other_stuff":"other data"}',
                    'my_special_format_field' => '{"format":"filtered_html","other_stuff":"other data"}',
                ],
            ],
        ];
        $localizer->localizeEntity($data);

        $expected = [
            'value' => [
                'und' => [
                    'single_value_field' => 'single_value_data',
                    'one_address_field' => '{"country":"country_code_data","thoroughfare":"address_line1_data","premise":"address_line2_data","organisation_name":"organization_data","first_name":"given_name_data","last_name":"family_name_data"}',
                    'internal_link_field' => '{"url":"my_internal_uri"}',
                    'external_link_field1' => '{"url":"my_internal_uri"}',
                    'external_link_field2' => '{"url":"my_interinternal:\/nal_uri"}',
                ],
                'de' => [
                    'single_value_field' => 'single_value_data',
                    'restricted_html_format_field' => '{"format":"filtered_html","other_stuff":"other data"}',
                    'basic_html_format_field' => '{"format":"filtered_html","other_stuff":"other data"}',
                    'full_html_format_field' => '{"format":"full_html","other_stuff":"other data"}',
                    'rich_text_format_field' => '{"format":"full_html","other_stuff":"other data"}',
                    'my_special_format_field' => '{"format":"filtered_html","other_stuff":"other data"}',
                ],
            ],
        ];
        $this->assertEquals($expected, $data);
    }

}
