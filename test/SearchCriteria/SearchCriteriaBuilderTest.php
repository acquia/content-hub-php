<?php

namespace Acquia\ContentHubClient\test\SearchCriteria;

use Acquia\ContentHubClient\CDF\CDFObject;
use Acquia\ContentHubClient\SearchCriteria\SearchCriteria;
use Acquia\ContentHubClient\SearchCriteria\SearchCriteriaBuilder;
use PHPUnit\Framework\TestCase;

/**
 * Class SearchCriteriaBuilderTest.
 *
 * @covers \Acquia\ContentHubClient\SearchCriteria\SearchCriteriaBuilder
 *
 * @package Acquia\ContentHubClient\test\SearchCriteria
 */
class SearchCriteriaBuilderTest extends TestCase {

  /**
   * Search criteria data.
   *
   * @var array
   */
  private $search_criteria_data; // phpcs:ignore

  /**
   * Default search criteria data.
   *
   * @var array
   */
  private $default_search_criteria_data; // phpcs:ignore

  /**
   * {@inheritDoc}
   */
  protected function setUp() {
    parent::setUp();

    $this->default_search_criteria_data = [
      'search_term' => SearchCriteria::DEFAULT_SEARCH_TERM,
      'type' => [],
      'bundle' => [],
      'tags' => [],
      'label' => '',
      'start_date' => NULL,
      'end_date' => NULL,
      'from' => SearchCriteria::DEFAULT_OFFSET,
      'size' => SearchCriteria::DEFAULT_LIMIT,
      'sorting' => '',
      'version' => SearchCriteria::DEFAULT_VERSION,
      'languages' => [CDFObject::LANGUAGE_UNDETERMINED],
    ];

    $this->search_criteria_data = [
      'search_term' => 'some-search-term',
      'type' => ['type1', 'type2'],
      'bundle' => ['bundle1', 'bundle2', 'bundle3'],
      'tags' => ['tag1', 'tag2'],
      'label' => 'some-label',
      'start_date' => new \DateTimeImmutable(),
      'end_date' => new \DateTimeImmutable(),
      'from' => 10,
      'size' => 20,
      'sorting' => '',
      'version' => 'some-version',
      'languages' => ['lang1'],
    ];
  }

  /**
   * {@inheritDoc}
   */
  protected function tearDown() {
    parent::tearDown();
  }

  /**
   * Tests SearchCriteria creation with all required params.
   */
  public function testObjectCreationWithAllRequiredParams(): void {
    $search_criteria = SearchCriteriaBuilder::createFromArray($this->search_criteria_data);

    $this->assertEquals($search_criteria->jsonSerialize(), $this->search_criteria_data);
  }

  /**
   * Tests SearchCriteria creation with all default params.
   */
  public function testObjectCreationWithAllDefaultParams(): void {
    $search_criteria = SearchCriteriaBuilder::createFromArray($this->default_search_criteria_data);

    $this->assertEquals($search_criteria->jsonSerialize(), $this->default_search_criteria_data);
  }

  /**
   * Tests SearchCriteria creation from empty array.
   */
  public function testObjectCreationWithUnsetParamsUsesDefault(): void {
    $search_criteria = SearchCriteriaBuilder::createFromArray([]);

    $this->assertEquals($search_criteria->jsonSerialize(), $this->default_search_criteria_data);
  }

  /**
   * Tests language switching.
   */
  public function testSetLanguagesOnSearchCriteriaChangesLanguages(): void {
    $new_languages = ['new-lang1', 'new-lang2'];

    $search_criteria = SearchCriteriaBuilder::createFromArray($this->search_criteria_data);
    $this->assertNotEquals($search_criteria->getLanguages(), $new_languages);

    $search_criteria->setLanguages($new_languages);

    $this->assertEquals($search_criteria->getLanguages(), $new_languages);
  }

}
