<?php

namespace Acquia\ContentHubClient;

use Acquia\ContentHubClient\CDF\CDFObject;

/**
 * Class CDFDocument.
 *
 * @package Acquia\ContentHubClient
 */
class CDFDocument
{

    /**
     * An array of entity dependencies keyed by parent entity uuid.
     *
     * @var array
     */
    protected $dependencies = [];

    /**
     * An array of entity ancestors keyed by the child entity uuid.
     *
     * @var array
     */
    protected $ancestors = [];

    /**
     * Entities list.
     *
     * @var \Acquia\ContentHubClient\CDF\CDFObject[]
     */
    protected $entities;

    /**
     * CDFDocument constructor.
     *
     * @param \Acquia\ContentHubClient\CDF\CDFObject[] $entities
     *   Entities list.
     */
    public function __construct(CDFObject ...$entities)
    {
        $this->setCdfEntities(...$entities);
    }

    /**
     * Returns entities list.
     *
     * @return \Acquia\ContentHubClient\CDF\CDFObject[]
     *   Entities list.
     */
    public function getEntities()
    {
        return $this->entities;
    }

    /**
     * Returns entity by UUID.
     *
     * @param string $uuid
     *   Entity UUID.
     *
     * @return \Acquia\ContentHubClient\CDF\CDFObject|null
     *   CDFObject if exists, otherwise NULL
     */
    public function getCdfEntity($uuid)
    {
        return $this->entities[$uuid] ?? null;
    }

    /**
     * Entities setter.
     *
     * @param \Acquia\ContentHubClient\CDF\CDFObject[] $entities
     *   Entities list.
     */
    public function setCdfEntities(CDFObject ...$entities)
    {
        $this->entities = [];

        foreach ($entities as $entity) {
            $this->addCdfEntity($entity);
        }
    }

    /**
     * Appends CDF object to document.
     *
     * @param \Acquia\ContentHubClient\CDF\CDFObject $object
     *   CDF object.
     */
    public function addCdfEntity(CDFObject $object)
    {
        $this->entities[$object->getUuid()] = $object;
        $this->generateDependencyMap($object);
    }

    /**
     * Removes entity from document.
     *
     * @param string $uuid
     *   Entity UUID.
     */
    public function removeCdfEntity($uuid)
    {
        $children = $this->getDependencies($uuid);

        unset($this->entities[$uuid], $this->dependencies[$uuid]);

        foreach ($children as $child) {
            unset($this->ancestors[$child][$uuid]);

            if (empty($this->getDependencies($child)) && empty($this->getAncestors($child))) {
                $this->removeCdfEntity($child);
            }
        }
    }

    /**
     * Checks if document contains entities.
     *
     * @return bool
     *   TRUE if entities exists, otherwise FALSE.
     */
    public function hasEntities()
    {
        return (bool)$this->entities;
    }

    /**
     * Checks that entity exists in document.
     *
     * @param string $uuid
     *   Entity UUID.
     *
     * @return bool
     *   TRUE if entity exists, otherwise FALSE.
     */
    public function hasEntity($uuid)
    {
        return !empty($this->entities[$uuid]);
    }

    /**
     * Merges CDF document into current.
     *
     * @param \Acquia\ContentHubClient\CDFDocument $document
     *   CDF document.
     */
    public function mergeDocuments(CDFDocument $document)
    {
        foreach ($document->getEntities() as $entity) {
            $this->addCdfEntity($entity);
        }
    }

    /**
     * Converts CDF document to string (JSON).
     *
     * @return false|string
     *   String representation.
     */
    public function toString()
    {
        $entities = [];
        foreach ($this->getEntities() as $entity) {
            $entities[] = $entity->toArray();
        }
        $output = ['entities' => $entities];

        return json_encode($output, JSON_PRETTY_PRINT);
    }

    protected function generateDependencyMap(CDFObject $entity) {
        $uuid = $entity->getUuid();
        $this->dependencies[$uuid] = array_keys($entity->getMetadata()['dependencies']['entity'] ?? []);

        foreach ($this->dependencies[$uuid] as $child_uuid) {
            $this->ancestors[$child_uuid][$uuid] = $uuid;
        }
    }

    public function getDependencies(string $uuid): array {
        return $this->dependencies[$uuid] ?? [];
    }

    public function getAncestors(string $uuid): array {
        return $this->ancestors[$uuid] ?? [];
    }

}
