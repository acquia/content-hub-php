<?php

namespace Acquia\ContentHubClient\Data\Formatter\Localizer;

use Acquia\ContentHubClient\Data\Exception\UnsupportedFormatException;

/**
 * Abstract data localizer class.
 */
abstract class AbstractLocalizer implements LocalizerInterface
{
    /**
     * Config.
     *
     * @var array
     */
    protected $config = [];

    /**
     * AbstractLocalizer constructor.
     *
     * @param array $config Config
     */
    public function __construct(array $config = [])
    {
        $this->config += $config;
    }

    /**
     * Get localizer class name.
     *
     * @param string $type Type
     * @param string $kind Kind
     *
     * @return string
     */
    protected function getLocalizerClassName($type, $kind)
    {
        $className = str_replace(['_', '<', '>'], '', ucwords($type, '_<>'));
        return __NAMESPACE__ . '\\' . $kind . '\\Drupal7\\' . $className;
    }

    /**
     * {@inheritdoc}
     *
     * @throws \Acquia\ContentHubClient\Data\Exception\UnsupportedFormatException
     */
    public function localize($data, array $config = [])
    {
        if (empty($config['dataType'])) {
            throw new UnsupportedFormatException('The localization must know data\'s type.');
        }

        $dataType = $config['dataType'];
        $functionName = 'localize' . $dataType;

        if (!method_exists($this, $functionName)) {
            // If you are here, you should now create the function in the
            // corresponding concrete classes to support the localization.
            throw new UnsupportedFormatException('The following data type\'s localization is not yet supported: ' . $dataType);
        }

        $this->$functionName($data, $config);

        return $data;
    }

}
