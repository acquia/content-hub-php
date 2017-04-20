<?php

namespace Acquia\ContentHubClient\Data\Formatter\Standardizer;

/**
 * Standardizer interface.
 */
interface StandardizerInterface
{
    /**
     * Standardize - takes the data, and reformat into the universal format.
     *
     * @param mixed $data Data
     * @param array $config Config
     *
     * @return mixed
     */
    public function standardize($data, array $config = []);
}
