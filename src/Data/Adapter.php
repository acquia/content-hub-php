<?php

namespace Acquia\ContentHubClient\Data;

use Acquia\ContentHubClient\Data\Exception\UnsupportedMappingException;
use Acquia\ContentHubClient\Data\Exception\DataAdapterException;

class Adapter
{
    private $config;
    private $mappers;

    /**
     * Constructor.
     *
     * @param array $config
     *
     * @throws \Acquia\ContentHubClient\Data\Exception\UnsupportedMappingException
     */
    public function __construct(array $config = [])
    {
        if (!isset($config['schemaId'])) {
          $config['schemaId'] = 'None';
        }

        if ($config['schemaId'] !== 'None' && !class_exists(__NAMESPACE__ . '\\Mapper\\' . $config['schemaId'])) {
            throw new UnsupportedMapperException('The localized data schema is not yet supported: ' . $config['schemaId']);
        }

        $this->config = $config;
    }

    public function translate($data, $config)
    {
        $adapterSchema = $this->config['schemaId'];
        if ($adapterSchema === 'None') {
            return $data;
        }

        $dataSchemaId = $this->getSchemaId($data, $config);

        if ($adapterSchema === $dataSchemaId) {
            return $data;
        }

        $standardizedData = $this->getMapper($dataSchemaId)->standardize($data, $config);
        return $this->getMapper($adapterSchema)->localize($standardizedData, $config);
    }

    private function getSchemaId($data, $config)
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

        // Detect Mixed.
        if ($config['dataType'] === 'ListEntities') {
            return 'Mixed';
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
            $this->mappers[$schemaId] = new $mapperClassName($this->config);
        }

        return $this->mappers[$schemaId];
    }

}
