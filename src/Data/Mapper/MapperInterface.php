<?php

namespace Acquia\ContentHubClient\Data\Mapper;

interface MapperInterface
{
    public function standardize($data, $config);

    public function localize($data, $config);
}
