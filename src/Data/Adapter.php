<?php

namespace Acquia\ContentHubClient\Data;

use Acquia\ContentHubClient\Data\Exception\UnsupportedMappingException;
use Acquia\ContentHubClient\Data\Exception\DataAdapterException;

class Adapter
{
    private $schemaId;
    private $mappers;

    /**
     * Constructor.
     *
     * @param string $schemaId
     *
     * @throws \Exception
     */
    public function __construct($schemaId = 'None')
    {
        if ($schemaId !== 'None' && !class_exists(__NAMESPACE__ . '\\Mapper\\' . $schemaId)) {
            throw new UnsupportedMapperException('The localized data schema is not yet supported: ' . $schemaId);
        }

        $this->schemaId = $schemaId;
    }

    public function translate($data, $config)
    {
        if ($this->schemaId === 'None') {
            return $data;
        }

        $dataSchemaId = $this->getSchemaId($data);

        if ($this->schemaId === $dataSchemaId) {
            return $data;
        }

        $standardized_data = $this->getMapper($dataSchemaId)->standardize($data, $config);
        return $this->getMapper($this->schemaId)->localize($standardized_data, $config);
    }

    private function getSchemaId($data)
    {
        // Detect already defined schema.
        if (!empty($data['metadata']['schema'])) {
            return $data['metadata']['schema'];
        }

        // Detect Drupal 7.
        if (isset($data['attributes']['language']['value'])) {
            return 'Drupal7';
        }

        // Detect Drupal 8.
        if (isset($data['attributes']['langcode']['value'])) {
            return 'Drupal8';
        }

        // Shouldn't reach here.
        throw new DataAdapterException('The data adapter could not determine the data\'s schema ID.');
    }

    private function getMapper($schemaId) {
        $mapperClassName = __NAMESPACE__ . '\\Mapper\\' . $schemaId;
        if (!class_exists($mapperClassName)) {
            throw new UnsupportedMappingException('The data schema is not yet supported: ' . $schemaId);
        }

        if (!isset($this->mappers[$schemaId])) {
            $this->mappers[$schemaId] = new $mapperClassName();
        }

        return $this->mappers[$schemaId];
    }

}
