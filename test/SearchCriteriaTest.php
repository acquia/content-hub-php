<?php

namespace Acquia\ContentHubClient\test;

use PHPUnit\Framework\TestCase;
use Acquia\ContentHubClient\SearchCriteria\Transformer;

/**
 * @coversDefaultClass \Acquia\ContentHubClient\SearchCriteria\Transformer
 */
class SearchCriteriaTest extends TestCase {

  /**
   * @var array
   */
  private $data;

  /**
   * @var array
   */
  private $expected;

  /**
   * @before
   */
  public function setUp() {
    $this->data = [
      'search_term' => 'some-search-term',
      'type' => 'some-type',
      'bundle' => 'some-bundle',
      'tags' => 'some-tags',
      'label' => 'some-label',
      'start_date' => 'some-start_date',
      'end_date' => 'some-end-date',
      'from' => 'some-from',
      'size' => 'some-size',
      'sorting' => 'some-sort',
      'version' => 'some-version',
      'languages' => [
        'some-language-1',
        'some-language-2',
      ],
    ];

    $this->expected = [
      'search_term' => 'some-search-term',
      'type' =>
        [
          0 => 'some-type',
        ],
      'bundle' =>
        [
          0 => 'some-bundle',
        ],
      'tags' =>
        [
          0 => 'some-tags',
        ],
      'label' => 'some-label',
      'start_date' => 'some-start_date',
      'end_date' => 'some-end-date',
      'from' => 'some-from',
      'size' => 'some-size',
      'sorting' => '',
      'version' => 'some-version',
      'languages' =>
        [
          0 => 'some-language-1',
          1 => 'some-language-2',
        ],
    ];
  }

  /**
   * @covers ::arrayToSearchCriteriaArray
   * @preserveGlobalState disabled
   */
  public function testResultWhenAllKeysArePresentInData(): void {
    $data = $this->data;
    $expected = $this->expected;

    self::assertEquals(Transformer::arrayToSearchCriteriaArray($data), $expected);
  }

  /**
   * @covers ::arrayToSearchCriteriaArray
   * @preserveGlobalState disabled
   */
  public function testMissingKeysGetDefaultValues(): void {
    $data = $this->data;
    $expected = $this->expected;

    foreach (array_keys($this->data) as $non_existing_key) {
      unset($data[$non_existing_key]);
      $expected[$non_existing_key] = Transformer::$supported_keys[$non_existing_key]['default_value'];
    }

    self::assertEquals(Transformer::arrayToSearchCriteriaArray($data), $expected);
  }

  /**
   * @covers ::arrayToSearchCriteriaArray
   * @preserveGlobalState disabled
   */
  public function testKeysWithAlternatePossibleKeysReceiveCorrectData(): void {
    $data = $this->data;
    $expected = $this->expected;

    $keys_with_alternate = [
      'from' => 'start',
      'size' => 'limit',
    ];

    foreach ($keys_with_alternate as $key => $alternate_key) {
      $data[$alternate_key] = $data[$key];
      unset($data[$key]);
    }

    self::assertEquals(Transformer::arrayToSearchCriteriaArray($data), $expected);
  }

  /**
   * @covers ::arrayToSearchCriteriaArray
   * @preserveGlobalState disabled
   */
  public function testIfDefaultValueIsArrayValueWillAlwaysBeArray(): void {
    $data = $this->data;
    $expected = $this->expected;

    $data['languages'] = 'en';
    $expected['languages'] = ['en'];

    self::assertEquals(Transformer::arrayToSearchCriteriaArray($data), $expected);
  }

}
