<?php

namespace Acquia\ContentHubClient\SearchCriteria;

use DateTimeInterface;

/**
 * Class SearchCriteria
 *
 * @package Drupal\acquia_contenthub\Client
 */
class SearchCriteria implements \JsonSerializable
{
    const HEADER_NAME = 'X-Search-Criteria';

    const DEFAULT_SEARCH_TERM = 'drupal8_content_entity';

    const DEFAULT_OFFSET = 0;

    const DEFAULT_LIMIT = 1000;

    const DEFAULT_VERSION = '2.0';

    /**
     * @var string
     */
    protected $searchTerm;

    /**
     * @var array
     */
    protected $entityType;

    /**
     * @var array
     */
    protected $bundle;

    /**
     * @var array
     */
    protected $tags;

    /**
     * @var string
     */
    protected $label;

    /**
     * @var \DateTimeInterface
     */
    protected $startDate;

    /**
     * @var \DateTimeInterface
     */
    protected $endDate;

    /**
     * @var int
     */
    protected $from;

    /**
     * @var int
     */
    protected $size;

    /**
     * @var string
     */
    protected $sorting;

    /**
     * @var string
     */
    protected $version;

    /**
     * SearchCriteria constructor.
     *
     * @param string $searchTerm
     * @param array $entityType
     * @param array $bundle
     * @param array $tags
     * @param string $label
     * @param \DateTimeInterface $startDate
     * @param \DateTimeInterface $endDate
     * @param int $from
     * @param int $size
     * @param string $sorting
     * @param string $version
     */
    public function __construct(string $searchTerm, array $entityType, array $bundle, array $tags, string $label, ?DateTimeInterface $startDate, ?DateTimeInterface $endDate, int $from, int $size, string $sorting, string $version) // @codingStandardsIgnoreLine.
    {
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
    }

    /**
     * {@inheritDoc}
     */
    public function jsonSerialize()
    {
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
        ];
    }

    /**
     * @return string
     */
    public function getSearchTerm(): string
    {
        return $this->searchTerm;
    }

    /**
     * @return array
     */
    public function getEntityType(): array
    {
        return $this->entityType;
    }

    /**
     * @return array
     */
    public function getBundle(): array
    {
        return $this->bundle;
    }

    /**
     * @return array
     */
    public function getTags(): array
    {
        return $this->tags;
    }

    /**
     * @return string
     */
    public function getLabel(): string
    {
        return $this->label;
    }

    /**
     * @return \DateTimeInterface|null
     */
    public function getStartDate(): ?DateTimeInterface
    {
        return $this->startDate;
    }

    /**
     * @return \DateTimeInterface|null
     */
    public function getEndDate(): ?DateTimeInterface
    {
        return $this->endDate;
    }

    /**
     * @return int
     */
    public function getFrom(): int
    {
        return $this->from;
    }

    /**
     * @return int
     */
    public function getSize(): int
    {
        return $this->size;
    }

    /**
     * @return string
     */
    public function getSorting(): string
    {
        return $this->sorting;
    }

    /**
     * @return string
     */
    public function getVersion(): string
    {
        return $this->version;
    }
}
