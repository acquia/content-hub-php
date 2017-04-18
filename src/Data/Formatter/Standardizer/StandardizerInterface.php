<?php

namespace Acquia\ContentHubClient\Data\Formatter\Standardizer;

/**
 * Standardizer interface.
 */
interface StandardizerInterface
{
    /**
     * @param mixed $data
     * @param array $config
     *
     * @return mixed
     */
    public function standardize($data, array $config = []);
}
