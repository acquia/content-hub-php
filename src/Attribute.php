<?php

namespace Acquia\ContentServicesClient;

class Attribute extends \ArrayObject
{
    /**
     * Default language when no language is given.
     */
    const LANGUAGE_DEFAULT = 'und';

    /**
     * @var \Acquia\ContentServicesClient\TypeHandler[]
     */
    protected $handlers = array();

    /**
     * Attribute's Constructor.
     *
     * If no type is set, a default type of 'string' is assumed.
     *
     * @param string $type
     * @throws \Exception
     */
    public function __construct($type = 'string')
    {
        $this->setTypeHandler(new TypeHandler('integer', 'integer'))
             ->setTypeHandler(new TypeHandler('string', 'string'))
             ->setTypeHandler(new TypeHandler('boolean', 'boolean'))
             ->setTypeHandler(new TypeHandler('number', 'float'))
             ->setTypeHandler(new TypeHandler('reference', 'string'))
             ->setTypeHandler(new TypeHandler('array<integer>', 'integer'))
             ->setTypeHandler(new TypeHandler('array<string>', 'string'))
             ->setTypeHandler(new TypeHandler('array<boolean>', 'boolean'))
             ->setTypeHandler(new TypeHandler('array<number>', 'float'))
             ->setTypeHandler(new TypeHandler('array<reference>', 'string'))
        ;

        // Validate that this attribute type can be handled.
        if (!in_array($type, $this->getTypeHandlers())) {
          throw new \Exception('Type handler not registered for this type: ' . $type);
        }
        $array = [
          'type'  => $type,
          'value' => [],
        ];
        parent::__construct($array);
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
     * @param array|bool|string|float|integer          $value
     * @param string                                   $lang
     *
     * @return \Acquia\ContentServicesClient\Attribute
     */
    public function setValue($value, $lang = self::LANGUAGE_DEFAULT)
    {
        $this['value'][$lang] = $this->getTypeHandler()->set($value);
        return $this;
    }

    /**
     * @param array $value
     *
     * @return \Acquia\ContentServicesClient\Attribute
     */
    public function setValues(array $value)
    {
        foreach ($value as $lang => $val) {
            $this->setValue($val, $lang);
        }
        return $this;
    }


    /**
     * Returns the value in a specific language
     *
     * @param $lang
     *    The language of the attribute.
     * @return mixed
     */
    public function getValue($lang = self::LANGUAGE_DEFAULT)
    {
      if (isset($this->getVal('value')[$lang])) {
          return $this->getVal('value')[$lang];
      }
      else {
          return isset($this->getVal('value')[self::LANGUAGE_DEFAULT]) ? $this->getVal('value')[self::LANGUAGE_DEFAULT] : NULL;
      }
    }

    /**
     *
     * @return array
     */
    public function getValues()
    {
        return $this->getVal('value', []);
    }

    /**
     * @param string $key
     * @param string $default
     *
     * @return mixed
     */
    protected  function getVal($key, $default = NULL)
    {
        return isset($this[$key]) ? $this[$key] : $default;
    }

    /**
     * Registers a TypeHandler.
     *
     * @param TypeHandler $typeHandler
     * @return $this
     */
    public function setTypeHandler(TypeHandler $typeHandler)
    {
        $handlerName = $typeHandler->getType();
        $this->handlers[$handlerName] = $typeHandler;
        return $this;
    }

    /**
     * Returns the Type Handler.
     *
     * @return TypeHandler
     */
    public function getTypeHandler()
    {
        return $this->handlers[$this->getType()];
    }

    /**
     * Returns the types for all TypeHandlers registered.
     *
     * @return array
     */
    public function getTypeHandlers()
    {
        return array_keys($this->handlers);
    }

}
