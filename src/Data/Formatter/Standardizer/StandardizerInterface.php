<?php

namespace Acquia\ContentHubClient\Data\Formatter\Standardizer;

interface StandardizerInterface
{
    public function standardize($data, array $config = []);
}
