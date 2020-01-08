<?php

namespace Acquia\ContentHubClient\SearchCriteria;

use DateTimeInterface;

/**
 * Class SearchCriteria.
 *
 * @package Drupal\acquia_contenthub\Client
 */
class SearchCriteria implements \JsonSerializable {

  const HEADER_NAME = 'X-Acquia-Content-Hub-Search-Criteria';

  const DEFAULT_SEARCH_TERM = 'drupal8_content_entity';

  const DEFAULT_OFFSET = 0;

  const DEFAULT_LIMIT = 1000;

  const DEFAULT_VERSION = '2.0';

  /**
   * Search term.
   *
   * @var string
   */
  protected $searchTerm;

  /**
   * Entity types.
   *
   * @var array
   */
  protected $entityType;

  /**
   * Entity bundles.
   *
   * @var array
   */
  protected $bundle;

  /**
   * Entity tags list.
   *
   * @var array
   */
  protected $tags;

  /**
   * Entity label.
   *
   * @var string
   */
  protected $label;

  /**
   * Start date of the search interval.
   *
   * @var \DateTimeInterface
   */
  protected $startDate;

  /**
   * End date of the search interval.
   *
   * @var \DateTimeInterface
   */
  protected $endDate;

  /**
   * Search offset.
   *
   * @var int
   */
  protected $from;

  /**
   * Search limit.
   *
   * @var int
   */
  protected $size;

  /**
   * Sorting order.
   *
   * @var string
   */
  protected $sorting;

  /**
   * Search version.
   *
   * @var string
   */
  protected $version;

  /**
   * Languages list.
   *
   * @var array
   */
  protected $languages;

  /**
   * SearchCriteria constructor.
   *
   * @param string $searchTerm
   *   Search term.
   * @param array $entityType
   *   Entity types.
   * @param array $bundle
   *   Bundles list.
   * @param array $tags
   *   Tags list.
   * @param string $label
   *   Entity label.
   * @param \DateTimeInterface|null $startDate
   *   Start date of the search interval.
   * @param \DateTimeInterface|null $endDate
   *   End date of the search interval.
   * @param int $from
   *   Search offset.
   * @param int $size
   *   Search limit.
   * @param string $sorting
   *   Sorting order.
   * @param string $version
   *   Search version.
   * @param array $languages
   *   Languages list.
   */
  public function __construct(
    string $searchTerm,
    array $entityType,
    array $bundle,
    array $tags,
    string $label,
    ?DateTimeInterface $startDate,
    ?DateTimeInterface $endDate,
    int $from,
    int $size,
    string $sorting,
    string $version,
    array $languages
  ) {
    $this->searchTerm = $searchTerm;
    $this->entityType = $entityType;
    $this->bundle = $bundle;
    $this->tags = $tags;
    $this->label = $label;
    $this->startDate = $startDate;
    $this->endDate = $endDate;
    $this->from = $from;
    $this->size = $size;
    $this->sorting = $sorting;
    $this->version = $version;
    $this->languages = $languages;
  }

  /**
   * {@inheritDoc}
   */
  public function jsonSerialize() {
    return [
      'search_term' => $this->getSearchTerm(),
      'type' => $this->getEntityType(),
      'bundle' => $this->getBundle(),
      'tags' => $this->getTags(),
      'label' => $this->getLabel(),
      'start_date' => $this->getStartDate(),
      'end_date' => $this->getEndDate(),
      'from' => $this->getFrom(),
      'size' => $this->getSize(),
      'sorting' => $this->getSorting(),
      'version' => $this->getVersion(),
      'languages' => $this->getLanguages(),
    ];
  }

  /**
   * Search term getter.
   *
   * @return string
   *   Search term.
   */
  public function getSearchTerm(): string {
    return $this->searchTerm;
  }

  /**
   * Entity types getter.
   *
   * @return array
   *   Entity types list.
   */
  public function getEntityType(): array {
    return $this->entityType;
  }

  /**
   * Bundle getter.
   *
   * @return array
   *   Bundles list.
   */
  public function getBundle(): array {
    return $this->bundle;
  }

  /**
   * Tags getter.
   *
   * @return array
   *   Tags list.
   */
  public function getTags(): array {
    return $this->tags;
  }

  /**
   * Label getter.
   *
   * @return string
   *   Label.
   */
  public function getLabel(): string {
    return $this->label;
  }

  /**
   * Start date getter.
   *
   * @return \DateTimeInterface|null
   *   Search start date.
   */
  public function getStartDate(): ?DateTimeInterface {
    return $this->startDate;
  }

  /**
   * End date getter.
   *
   * @return \DateTimeInterface|null
   *   Search end date.
   */
  public function getEndDate(): ?DateTimeInterface {
    return $this->endDate;
  }

  /**
   * Returns number of items that should be skipped before selection.
   *
   * @return int
   *   A number of items that should be skipped before selection.
   */
  public function getFrom(): int {
    return $this->from;
  }

  /**
   * Returns how many items should be selected.
   *
   * @return int
   *   Number of items that should be selected.
   */
  public function getSize(): int {
    return $this->size;
  }

  /**
   * Sorting getter.
   *
   * @return string
   *   Sorting value.
   */
  public function getSorting(): string {
    return $this->sorting;
  }

  /**
   * Version getter.
   *
   * @return string
   *   Version string.
   */
  public function getVersion(): string {
    return $this->version;
  }

  /**
   * Languages getter.
   *
   * @return array
   *   Languages list.
   */
  public function getLanguages(): array {
    return $this->languages;
  }

  /**
   * Languages setter.
   *
   * @param array $languages
   *   Languages list.
   */
  public function setLanguages(array $languages): void {
    $this->languages = $languages;
  }

}
