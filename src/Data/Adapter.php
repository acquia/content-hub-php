<?php

namespace Acquia\ContentHubClient\Data;

use Acquia\ContentHubClient\Data\Exception\UnsupportedFormatException;
use Acquia\ContentHubClient\Data\Exception\DataAdapterException;

/**
 * Data adapter class.
 */
class Adapter
{
    /**
     * Config.
     *
     * @var array
     */
    private $config;

    /**
     * Formatters.
     *
     * @var array
     */
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

    /**
     * Translate data into localized format.
     *
     * @param array $data   Data to be translated
     * @param array $config Additional function config
     *
     * @return array
     */
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

    /**
     * Get schema id by "cold reading" the data.
     *
     * @param array $data   Data to be "cold read"
     * @param array $config Additional function config
     *
     * @return string
     *
     * @throws \Acquia\ContentHubClient\Data\Exception\DataAdapterException
     */
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
        if (isset($config['dataType']) && $config['dataType'] === 'ListEntities') {
            return 'Mixed';
        }

        // Shouldn't reach here.
        throw new DataAdapterException('The data adapter could not determine the data\'s schema ID.');
    }

    /**
     * Get standardizer by the given schema ID.
     *
     * @param string $schemaId Schema Id
     *
     * @return \Acquia\ContentHubClient\Data\Formatter\Standardizer\StandardizerInterface
     */
    private function getStandardizer($schemaId)
    {
        $formaterClassName = 'Standardizer\\' . $schemaId;
        return $this->getFormatter($formaterClassName);
    }

    /**
     * Get localizer by the given schema ID.
     *
     * @param string $schemaId Schema Id
     *
     * @return \Acquia\ContentHubClient\Data\Formatter\Localizer\LocalizerInterface
     */
    private function getLocalizer($schemaId)
    {
        $formaterClassName = 'Localizer\\' . $schemaId;
        return $this->getFormatter($formaterClassName);
    }

    /**
     * Get formatter by formatter class name.
     *
     * @param string $formaterClassName
     *
     * @return \Acquia\ContentHubClient\Data\Formatter\Standardizer\StandardizerInterface|\Acquia\ContentHubClient\Data\Formatter\Localizer\LocalizerInterface
     *
     * @throws \Acquia\ContentHubClient\Data\Exception\UnsupportedFormatException
     */
    private function getFormatter($formaterClassName)
    {
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
