<?php


namespace bkanber\Translator;


/**
 * Class Translation
 * @package bkanber\Translator
 *
 * This class represents a database model, a representation of a single key/value translation in a single language.
 * Normally this data would be stored in the DB.
 */
class Translation
{

    protected $language;
    protected $key;
    protected $content;

    /**
     * Translation constructor.
     * @param $language
     * @param $key
     * @param $content
     */
    public function __construct($language, $key, $content)
    {
        $this->language = $language;
        $this->key = $key;
        $this->content = $content;
    }

    /**
     * @return mixed
     */
    public function getLanguage()
    {
        return $this->language;
    }

    /**
     * @param mixed $language
     */
    public function setLanguage($language)
    {
        $this->language = $language;
    }

    /**
     * @return mixed
     */
    public function getKey()
    {
        return $this->key;
    }

    /**
     * @param mixed $key
     */
    public function setKey($key)
    {
        $this->key = $key;
    }

    /**
     * @return mixed
     */
    public function getContent()
    {
        return $this->content;
    }

    /**
     * @param mixed $content
     */
    public function setContent($content)
    {
        $this->content = $content;
    }




}