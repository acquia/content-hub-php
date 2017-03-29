<?php

namespace Acquia\ContentHubClient\Data\Mapper;

use Acquia\ContentHubClient\Data\Exception\UnsupportedMappingException;

abstract class Mappable implements MapperInterface
{
    protected $config = [];

    public function __construct(array $config = []) {
        $this->config += $config;
    }

    public function standardize($data, array $config = [])
    {
        if (empty($config['dataType'])) {
            throw new UnsupportedMappingException('The standardization must know data\'s type');
        }

        $dataType = $config['dataType'];
        $functionName = 'standardize' . $dataType;

        if (!method_exists($this, $functionName)) {
            throw new UnsupportedMappingException('The following data type\'s standardization is not yet supported: ' . $dataType);
        }

        return $this->$functionName($data, $config);
    }

    public function localize($data, array $config = [])
    {
        if (empty($config['dataType'])) {
            throw new UnsupportedMappingException('The localization must know data\'s type');
        }

        $dataType = $config['dataType'];
        $functionName = 'localize' . $dataType;

        if (!method_exists($this, $functionName)) {
            throw new UnsupportedMappingException('The following data type\'s localization is not yet supported: ' . $dataType);
        }

        return $this->$functionName($data, $config);
    }

}
