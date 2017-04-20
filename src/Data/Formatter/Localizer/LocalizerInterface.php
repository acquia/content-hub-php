<?php

namespace Acquia\ContentHubClient\Data\Formatter\Localizer;

/**
 * Localizer interface.
 */
interface LocalizerInterface
{
    /**
     * Localize.
     *
     * @param mixed $data Data
     * @param array $config Config
     *
     * @return mixed
     */
    public function localize($data, array $config = []);
}
