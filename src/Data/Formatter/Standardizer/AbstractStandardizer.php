<?php

namespace Acquia\ContentHubClient\Data\Formatter\Standardizer;

use Acquia\ContentHubClient\Data\Exception\UnsupportedFormatException;

/**
 * Abstract data standardizer class.
 */
abstract class AbstractStandardizer implements StandardizerInterface
{
    /**
     * Config.
     *
     * @var array
     */
    protected $config = [];

    /**
     * AbstractStandardizer constructor.
     *
     * @param array $config Config
     */
    public function __construct(array $config = [])
    {
        $this->config += $config;
    }

    /**
     * {@inheritdoc}
     *
     * @throws \Acquia\ContentHubClient\Data\Exception\UnsupportedFormatException
     */
    public function standardize($data, array $config = [])
    {
        if (empty($config['dataType'])) {
            throw new UnsupportedFormatException('The standardization must know data\'s type.');
        }

        $dataType = $config['dataType'];
        $functionName = 'standardize' . $dataType;

        if (!method_exists($this, $functionName)) {
            // If you are here, you should now create the function in the
            // corresponding concrete classes to support the standardization.
            throw new UnsupportedFormatException('The following data type\'s standardization is not yet supported: ' . $dataType);
        }

        $this->$functionName($data, $config);

        return $data;
    }

}
