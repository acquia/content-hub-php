<?php

namespace Acquia\ContentHubClient\Data\Formatter\Transformer;

/**
 * General data transformer class.
 */
class General
{
    /**
     * Rename.
     *
     * @param mixed $data Data to rename
     * @param mixed $fromIndex Rename from index
     * @param mixed $toIndex Rename to index
     */
    public function rename(&$data, $fromIndex, $toIndex)
    {
        $this->duplicate($data, $fromIndex, $toIndex);
        unset($data[$fromIndex]);
    }

    /**
     * Duplicate.
     *
     * @param mixed $data Data to duplicate
     * @param mixed $fromIndex Duplicate from index
     * @param mixed $toIndex Duplicate to index
     */
    public function duplicate(&$data, $fromIndex, $toIndex)
    {
        if (!isset($data[$fromIndex])) {
            return;
        }

        $data[$toIndex] = $data[$fromIndex];
    }

    /**
     * Multiple to Single.
     *
     * @param array  $data Data to convert from multiple to single
     * @param string $fromIndex From index
     * @param string $toIndex To index
     */
    public function multipleToSingle(&$data, $fromIndex, $toIndex)
    {
        if (!isset($data[$fromIndex])) {
            return;
        }

        foreach ($data[$fromIndex] as $language => $value) {
            $data[$toIndex][$language] = is_array($value) ? reset($value) : $value;
        }

        if ($fromIndex !== $toIndex) {
            unset($data[$fromIndex]);
        }
    }
}
