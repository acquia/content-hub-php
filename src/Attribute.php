<?php

namespace Acquia\ContentServicesClient;

class Attribute extends \ArrayObject
{
    /**
     * 
     * @param array $array
     */
    public function _construct(array $array = [])
    {
        $array += ['value' => []];
        parent::__construct($array);
    }
    
    /**
     * @param string $title
     * 
     * @return \Acquia\ContentServicesClient\Attribute
     */
    public function setTitle($title)
    {
        $this['title'] = $title;
        return $this;
    }
    
    /**
     * 
     * @return string
     */
    public function getTitle()
    {
        return $this->getVal('title', '');
    }
    
    /**
     * @param string $type
     * 
     * @return \Acquia\ContentServicesClient\Attribute
     */
    public function setType($type)
    {
        $this['type'] = $type;
        return $this;
    }
    
    /**
     * 
     * @return string
     */
    public function getType()
    {
        return $this->getVal('type', '');
    }
    
    /**
     * @param array $value
     * 
     * @return \Acquia\ContentServicesClient\Attribute
     */
    public function setValue(array $value)
    {
        $this['value'] = $value;
        return $this;
    }
    
    /**
     * 
     * @return array
     */
    public function getValue()
    {
        return $this->getVal('value', []);
    }

    /**
     * @param string $key
     * @param string $default
     * 
     * @return mixed
     */
    public function getVal($key, $default)
    {
        return isset($this[$key]) ? $this[$key] : $default;
    }
}
