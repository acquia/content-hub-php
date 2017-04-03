<?php

namespace Acquia\ContentHubClient\Data\Formatter\Transformer;

class General
{
    public function rename(&$data, $fromIndex, $toIndex)
    {
        if (!isset($data[$fromIndex])) {
            return;
        }

        $data[$toIndex] = $data[$fromIndex];
        unset($data[$fromIndex]);
    }

    public function duplicate(&$data, $fromIndex, $toIndex)
    {
        if (!isset($data[$fromIndex])) {
            return;
        }

        $data[$toIndex] = $data[$fromIndex];
    }

}
