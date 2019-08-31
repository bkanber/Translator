<?php


namespace bkanber\Translator\Parser;


use bkanber\Translator\Translatable;
use bkanber\Translator\Translation;

/**
 * Class AbstractParser
 * @package bkanber\Translator\Parser
 */
abstract class AbstractParser implements ParserInterface
{

    /**
     * Parse the input and generate Translatables.
     * @return Translatable[]
     */
    abstract public function parse();

    /**
     * Replace the translation keys in the input with the provided Translations
     * @param Translation[] $translations
     * @return mixed
     */
    abstract public function replace($translations);

    /** @var mixed */
    protected $input;

    /** @var Translatable[] */
    protected $translatables = [];

    /** @var Translation[] */
    protected $translations = [];

    /**
     * @return Translation[]
     */
    public function getTranslations()
    {
        return $this->translations;
    }

    /**
     * @param Translation[] $translations
     * @return static
     */
    public function setTranslations($translations)
    {
        $this->translations = $translations;
        return $this;
    }

    /**
     * @return mixed
     */
    public function getInput()
    {
        return $this->input;
    }

    /**
     * @param mixed $input
     * @return static
     */
    public function setInput($input)
    {
        $this->input = $input;
        return $this;
    }

    /**
     * @return Translatable[]
     */
    public function getTranslatables()
    {
        return $this->translatables;
    }

    /**
     * @param Translatable[] $translatables
     * @return static
     */
    public function setTranslatables($translatables)
    {
        $this->translatables = $translatables;
        return $this;
    }

    /**
     * @return array|string[]
     */
    public function getTranslatableKeys()
    {
        return array_values(
            array_unique(
                array_map(function (Translatable $translatable) {
                    return $translatable->getKey();
                }, $this->getTranslatables())
            )
        );
    }

    /**
     * @param $key
     * @return Translation|null
     */
    protected function findTranslation($key)
    {

        foreach ($this->getTranslations() as $translation) {
            if ($translation->getKey() === $key) {
                return $translation;
            }
        }

        return null;

    }



}
