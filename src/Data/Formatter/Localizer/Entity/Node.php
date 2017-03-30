<?php

namespace Acquia\ContentHubClient\Data\Formatter\Localizer\Entity;

class Node
{
    public function localizeEntity($data)
    {
        // Change language to langcode.
        if (isset($data['attributes']['langcode'])) {
            $data['attributes']['language'] = $data['attributes']['langcode'];
            unset($data['attributes']['langcode']);
        }

        return $data;
    }

}
