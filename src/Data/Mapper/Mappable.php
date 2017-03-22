<?php

namespace Acquia\ContentHubClient\Data\Mapper;

use Acquia\ContentHubClient\Data\Exception\UnsupportedMappingException;

abstract class Mappable implements MapperInterface
{
    public function standardize($data, $config = [])
    {
        $dataType = $config['dataType'];
        $functionName = 'standardize' . $dataType;

        if (!method_exists($this, $functionName)) {
            throw new UnsupportedMappingException('The following data type\'s standardization is not yet supported: ' . $dataType);
        }

        return $this->$functionName($data, $config);
    }

    public function localize($data, $config = [])
    {
        $dataType = $config['dataType'];
        $functionName = 'localize' . $dataType;

        if (!method_exists($this, $functionName)) {
            throw new UnsupportedMappingException('The following data type\'s localization is not yet supported: ' . $dataType);
        }

        return $this->$functionName($data, $config);
    }

}
