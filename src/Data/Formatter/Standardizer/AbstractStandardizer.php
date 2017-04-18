<?php

namespace Acquia\ContentHubClient\Data\Formatter\Standardizer;

use Acquia\ContentHubClient\Data\Exception\UnsupportedFormatException;

abstract class AbstractStandardizer implements StandardizerInterface
{
    protected $config = [];

    public function __construct(array $config = []) {
        $this->config += $config;
    }

    public function standardize($data, array $config = [])
    {
        if (empty($config['dataType'])) {
            throw new UnsupportedFormatException('The standardization must know data\'s type.');
        }

        $dataType = $config['dataType'];
        $functionName = 'standardize' . $dataType;

        if (!method_exists($this, $functionName)) {
            throw new UnsupportedFormatException('The following data type\'s standardization is not yet supported: ' . $dataType);
        }

        $this->$functionName($data, $config);

        return $data;
    }

}
