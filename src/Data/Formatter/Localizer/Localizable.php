<?php

namespace Acquia\ContentHubClient\Data\Formatter\Localizer;

use Acquia\ContentHubClient\Data\Exception\UnsupportedFormatException;

abstract class Localizable implements LocalizerInterface
{
    protected $config = [];

    public function __construct(array $config = []) {
        $this->config += $config;
    }

    public function localize($data, array $config = [])
    {
        if (empty($config['dataType'])) {
            throw new UnsupportedFormatException('The localization must know data\'s type.');
        }

        $dataType = $config['dataType'];
        $functionName = 'localize' . $dataType;

        if (!method_exists($this, $functionName)) {
            throw new UnsupportedFormatException('The following data type\'s localization is not yet supported: ' . $dataType);
        }

        return $this->$functionName($data, $config);
    }

}
