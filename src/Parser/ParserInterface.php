<?php

namespace bkanber\Translator\Parser;

use bkanber\Translator\Translatable;
use bkanber\Translator\Translation;

/**
 * Interface ParserInterface
 * @package bkanber\Translator\Parser
 */
interface ParserInterface
{

    /**
     * Returns the parser's input.
     * @return mixed
     */
    public function getInput();

    /**
     * Sets the parser's input.
     * @param $input
     * @return mixed
     */
    public function setInput($input);

    /**
     * Returns the array of Translatable objects that `parse` found.
     * @return Translatable[]
     */
    public function getTranslatables();

    /**
     * @param $translatables
     * @return mixed
     */
    public function setTranslatables($translatables);

    /**
     * @return Translation[]
     */
    public function getTranslations();

    /**
     * @param Translation[] $translations
     * @return mixed
     */
    public function setTranslations($translations);

    /**
     * Returns the unique translation keys found in the input.
     * @return array|string[]
     */
    public function getTranslatableKeys();

    /**
     * Parse the input and generate Translatables.
     * @return Translatable[]
     */
    public function parse();

    /**
     * Replace the translation keys in the input with the provided Translations
     * @param Translation[] $translations
     * @return mixed
     */
    public function replace($translations);
}
