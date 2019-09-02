<?php


namespace bkanber\Translator;


/**
 * Class Translatable
 * @package bkanber\Translator
 *
 * Simple class that represents a "translation key" in a string.
 * Translatables are found and created by the Parser classes, in order to keep track of what needs to be replaced.
 * At present this class is not much more than a 'struct'.
 */
class Translatable
{

    /** @var string */
    protected $replace;

    /** @var string */
    protected $key;

    /** @var string */
    protected $default;

    /**
     * Translatable constructor.
     * @param string $replace
     * @param string $key
     * @param string $default
     */
    public function __construct($replace, $key, $default)
    {
        $this->replace = $replace;
        $this->key = $key;
        $this->default = $default;
    }

    /**
     * @return string
     */
    public function getReplace()
    {
        return $this->replace;
    }

    /**
     * @param string $replace
     */
    public function setReplace($replace)
    {
        $this->replace = $replace;
    }

    /**
     * @return string
     */
    public function getKey()
    {
        return $this->key;
    }

    /**
     * @param string $key
     */
    public function setKey($key)
    {
        $this->key = $key;
    }

    /**
     * @return string
     */
    public function getDefault()
    {
        return $this->default;
    }

    /**
     * @param string $default
     */
    public function setDefault($default)
    {
        $this->default = $default;
    }

}
