<?php
/**
 * @file
 * Handles the supported data types for an Attribute.
 */

namespace Acquia\ContentHubClient;


class TypeHandler
{

    /**
     * The type
     *
     * @var string $type
     */
    protected $type;

    /**
     * The type to cast a value with.
     *
     * @var string $cast
     */
    protected $cast;

    /**
     * @param $type
     * @param $cast
     */
    public function __construct($type, $cast)
    {
        $this->type = $type;
        $this->cast = $cast;
    }

    /**
     * Gets the type.
     *
     * @return string
     */
    public function getType()
    {
        return $this->type;
    }

    /**
     * Obtains the type to cast the value with.
     *
     * @return string
     */
    public function getCast()
    {
        return $this->cast;
    }

    /**
     * Sets the value according to the type.
     *
     * @param $value
     * @return mixed
     */
    public function set($value)
    {
        if (is_array($value)) {
            foreach ($value as &$val) {
                settype($val, $this->getCast());
            }
        }
        else {
            if ($value !== NULL) {
                settype($value, $this->getCast());
            } else {
                return NULL;
            }
        }
        return $value;
    }

}
