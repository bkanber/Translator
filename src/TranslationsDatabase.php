<?php


namespace bkanber\Translator;


/**
 * Class TranslationsDatabase
 * @package bkanber\Translator
 */
class TranslationsDatabase
{

    /** @var Translation[] */
    protected $translations = [];

    /**
     * @param $definitions
     * @return TranslationsDatabase
     */
    public static function createFromArray($definitions)
    {
        $database = new static();
        $database->loadArray($definitions);

        return $database;
    }

    /**
     * @param array $definitions
     */
    public function loadArray($definitions)
    {

        foreach ($definitions as $definition) {
            $translation = new Translation($definition['language'], $definition['key'], $definition['content']);
            $this->translations[] = $translation;
        }

    }

    /**
     * @param $language
     * @param $key
     * @return Translation|null
     */
    public function findTranslation($language, $key)
    {
        foreach ($this->translations as $translation) {
            if ($translation->getLanguage() === $language && $translation->getKey() === $key) {
                return $translation;
            }
        }
        return null;
    }

}