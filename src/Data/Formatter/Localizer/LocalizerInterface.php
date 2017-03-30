<?php

namespace Acquia\ContentHubClient\Data\Formatter\Localizer;

interface LocalizerInterface
{
    public function localize($data, array $config = []);
}
