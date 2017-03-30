<?php

namespace Acquia\ContentHubClient\Data;

use Acquia\ContentHubClient\Data\Exception\UnsupportedFormatException;
use Acquia\ContentHubClient\Data\Exception\DataAdapterException;

class Adapter
{
    private $config;
    private $formatters;

    /**
     * Constructor.
     *
     * @param array $config
     *
     * @throws \Acquia\ContentHubClient\Data\Exception\UnsupportedFormatException
     */
    public function __construct(array $config = [])
    {
        if (!isset($config['schemaId'])) {
          $config['schemaId'] = 'None';
        }

        if ($config['schemaId'] !== 'None' && !class_exists(__NAMESPACE__ . '\\Formatter\\Localizer\\' . $config['schemaId'])) {
            throw new UnsupportedFormatException('The localized data schema is not yet supported: ' . $config['schemaId']);
        }

        $this->config = $config;
    }

    public function translate($data, $config)
    {
        $adapterSchemaId = $this->config['schemaId'];
        if ($adapterSchemaId === 'None') {
            return $data;
        }

        $dataSchemaId = $this->getSchemaId($data, $config);

        if ($adapterSchemaId === $dataSchemaId) {
            return $data;
        }

        $standardizedData = $this->getStandardizer($dataSchemaId)->standardize($data, $config);
        return $this->getLocalizer($adapterSchemaId)->localize($standardizedData, $config);
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

    private function getStandardizer($schemaId) {
        $formaterClassName = 'Standardizer\\' . $schemaId;
        return $this->getFormatter($formaterClassName);
    }

    private function getLocalizer($schemaId) {
        $formaterClassName = 'Localizer\\' . $schemaId;
        return $this->getFormatter($formaterClassName);
    }

    private function getFormatter($formaterClassName) {
        $formatterFullClassName = __NAMESPACE__ . '\\Formatter\\' . $formaterClassName;
        if (!class_exists($formatterFullClassName)) {
            throw new UnsupportedFormatException('This data formatting action is not yet supported: ' . $formaterClassName);
        }

        if (!isset($this->formatters[$formaterClassName])) {
            $this->formatters[$formaterClassName] = new $formatterFullClassName($this->config);
        }

        return $this->formatters[$formaterClassName];
    }

}
