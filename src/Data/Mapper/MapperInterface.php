<?php

namespace Acquia\ContentHubClient\Data\Mapper;

interface MapperInterface
{
    public function standardize($data, array $config = []);

    public function localize($data, array $config = []);
}
