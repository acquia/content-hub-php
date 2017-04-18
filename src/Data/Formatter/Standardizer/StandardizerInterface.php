<?php

namespace Acquia\ContentHubClient\Data\Formatter\Standardizer;

/**
 * Standardizer interface.
 */
interface StandardizerInterface
{
    /**
     * @param mixed $data Data
     * @param array $config Config
     *
     * @return mixed
     */
    public function standardize($data, array $config = []);
}
