<?php

namespace Acquia\ContentHubClient\test\SearchCriteria;

use Acquia\ContentHubClient\CDF\CDFObject;
use Acquia\ContentHubClient\SearchCriteria\SearchCriteria;
use Acquia\ContentHubClient\SearchCriteria\SearchCriteriaBuilder;
use PHPUnit\Framework\TestCase;


class SearchCriteriaBuilderTest extends TestCase {

  /**
   * @var array
   */
  private $search_criteria_data;

  /**
   * @var array
   */
  private $default_search_criteria_data;

  protected function setUp() {
    parent::setUp();

    $this->default_search_criteria_data = [
      'search_term' => SearchCriteria::DEFAULT_SEARCH_TERM,
      'type' => [],
      'bundle' => [],
      'tags' => [],
      'label' => '',
      'start_date' => null,
      'end_date' => null,
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

  protected function tearDown() {
    parent::tearDown();
  }

  public function testObjectCreationWithAllRequiredParams(): void {
    $search_criteria = SearchCriteriaBuilder::createFromArray($this->search_criteria_data);

    $this->assertEquals($search_criteria->jsonSerialize(), $this->search_criteria_data);
  }

  public function testObjectCreationWithAllDefaultParams(): void {
    $search_criteria = SearchCriteriaBuilder::createFromArray($this->default_search_criteria_data);

    $this->assertEquals($search_criteria->jsonSerialize(), $this->default_search_criteria_data);
  }

  public function testObjectCreationWithUnsetParamsUsesDefault(): void {
    $search_criteria = SearchCriteriaBuilder::createFromArray([]);

    $this->assertEquals($search_criteria->jsonSerialize(), $this->default_search_criteria_data);
  }

  public function testSetLanguagesOnSearchCriteriaChangesLanguages(): void {
    $new_languages = ['new-lang1', 'new-lang2'];

    $search_criteria = SearchCriteriaBuilder::createFromArray($this->search_criteria_data);
    $this->assertNotEquals($search_criteria->getLanguages(), $new_languages);

    $search_criteria->setLanguages($new_languages);

    $this->assertEquals($search_criteria->getLanguages(), $new_languages);
  }
}
