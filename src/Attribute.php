<?php

namespace Acquia\ContentHubClient;

class Attribute extends \ArrayObject
{
    /**
     * Default language when no language is given.
     */
    const LANGUAGE_DEFAULT     = 'und';

    /**
     *  Attribute's data types.
     */

    const TYPE_INTEGER         = 'integer';
    const TYPE_STRING          = 'string';
    const TYPE_KEYWORD         = 'keyword';
    const TYPE_BOOLEAN         = 'boolean';
    const TYPE_NUMBER          = 'number';
    const TYPE_REFERENCE       = 'reference';
    const TYPE_ARRAY_INTEGER   = 'array<integer>';
    const TYPE_ARRAY_STRING    = 'array<string>';
    const TYPE_ARRAY_KEYWORD   = 'array<keyword>';
    const TYPE_ARRAY_BOOLEAN   = 'array<boolean>';
    const TYPE_ARRAY_NUMBER    = 'array<number>';
    const TYPE_ARRAY_REFERENCE = 'array<reference>';

    /**
     * @var \Acquia\ContentHubClient\TypeHandler
     */
    protected $handler;

    /**
     * Attribute's Constructor.
     *
     * @param string $type
     * @throws \Exception
     */
    public function __construct($type)
    {
        // Validate that this attribute type can be handled.
        if (!in_array($type, $this->getTypeHandlers())) {
            throw new \Exception('Type handler not registered for this type: ' . $type);
        }

        $this->setTypeHandler(new TypeHandler($type, $this->getCastType($type)));
        $array = [
            'type'  => $type,
            'value' => [],
        ];
        parent::__construct($array);
    }

    /**
     * Obtains the type to cast the instance of that type.
     *
     * @param string $type
     *   The Attribute type.
     *
     * @return string
     *   The type to cast that attribute type.
     */
    protected function getCastType($type) {
        switch ($type) {
            case self::TYPE_INTEGER:
            case self::TYPE_STRING:
            case self::TYPE_BOOLEAN:
            case self::TYPE_NUMBER:
                return $type;

            case self::TYPE_ARRAY_INTEGER:
                return self::TYPE_INTEGER;

            case self::TYPE_ARRAY_STRING:
                return self::TYPE_STRING;

            case self::TYPE_ARRAY_BOOLEAN:
                return self::TYPE_BOOLEAN;

            case self::TYPE_ARRAY_NUMBER:
                return self::TYPE_NUMBER;

            case self::TYPE_ARRAY_KEYWORD:
            case self::TYPE_ARRAY_REFERENCE:
            case self::TYPE_KEYWORD;
            case self::TYPE_REFERENCE:
                return self::TYPE_STRING;

        }
    }

    /**
     * Returns the type.
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
     * @return \Acquia\ContentHubClient\Attribute
     */
    public function setValue($value, $lang = self::LANGUAGE_DEFAULT)
    {
        $this['value'][$lang] = $this->getTypeHandler()->set($value);
        return $this;
    }

    /**
     * @param array $value
     *
     * @return \Acquia\ContentHubClient\Attribute
     */
    public function setValues(array $value)
    {
        $this['value'] = [];
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
     * Returns the whole value array.
     *
     * @return array
     */
    public function getValues()
    {
        return $this->getVal('value', []);
    }

    /**
     * Removes a value for a specific language.
     *
     * @param string $lang
     * @return \Acquia\ContentHubClient\Attribute
     */
    public function removeValue($lang = self::LANGUAGE_DEFAULT)
    {
        $value = $this->getValues();
        unset($value[$lang]);
        $this->setValues($value);
        return $this;
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
     * Returns TRUE if the current attribute has values.
     *
     * @return bool
     */
    public function hasValues()
    {
        return (bool) count($this->getValues());
    }

    /**
     * Registers a TypeHandler.
     *
     * @param TypeHandler $typeHandler
     * @return $this
     */
    public function setTypeHandler(TypeHandler $typeHandler)
    {
        $this->handler = $typeHandler;
        return $this;
    }

    /**
     * Returns the Type Handler.
     *
     * @return TypeHandler
     */
    public function getTypeHandler()
    {
        return $this->handler;
    }

    /**
     * Returns the types for all possible TypeHandlers.
     *
     * @return array
     */
    public function getTypeHandlers()
    {
        return [
            self::TYPE_INTEGER,
            self::TYPE_STRING,
            self::TYPE_KEYWORD,
            self::TYPE_BOOLEAN,
            self::TYPE_NUMBER,
            self::TYPE_REFERENCE,
            self::TYPE_ARRAY_INTEGER,
            self::TYPE_ARRAY_STRING,
            self::TYPE_ARRAY_KEYWORD,
            self::TYPE_ARRAY_BOOLEAN,
            self::TYPE_ARRAY_NUMBER,
            self::TYPE_ARRAY_REFERENCE,
        ];
    }
}
