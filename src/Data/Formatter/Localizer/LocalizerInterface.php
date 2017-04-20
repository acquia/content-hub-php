<?php

namespace Acquia\ContentHubClient\Data\Formatter\Localizer;

/**
 * Localizer interface.
 */
interface LocalizerInterface
{
    /**
     * Localize - takes the data, and reformat into the local format.
     *
     * @param mixed $data Data
     * @param array $config Config
     *
     * @return mixed
     */
    public function localize($data, array $config = []);
}
