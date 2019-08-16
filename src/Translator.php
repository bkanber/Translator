<?php


namespace bkanber\Translator;


/**
 * Class Translator
 * @package bkanber\Translator
 *
 * This is the main translations management class.
 * In this example, the translator depends on a language and a 'database' object.
 * The database object here is a simple in-memory database. In a real implementation,
 * the database object might look different but needs to at least have a method that looks up a Translation by language and key.
 *
 * The two entrypoint methods for this class are `translateString` and `translateArray`.
 *
 * Example usage:
 *
 *   $database = TranslationsDatabase::createFromArray([['language' => 'en', 'key' => 'some_key', 'content' => 'hello world']]);
 *   $translator = new Translator('en', $database);
 *
 *   $translatedString = $translator->translateString("In english: __{{some_key, 'default text'}}");
 *   $translatedArray = $translator->translateArray(['description' => '__{{desc, "default description"}}']);
 */
class Translator
{

    /** @var string */
    protected $language;
    /** @var TranslationsDatabase */
    protected $database;

    /**
     * Translator constructor.
     * @param $language
     * @param $database
     */
    public function __construct($language, $database)
    {
        $this->language = $language;
        $this->database = $database;
    }

    /**
     * @return mixed
     */
    public function getLanguage()
    {
        return $this->language;
    }

    /**
     * @param string $language
     */
    public function setLanguage($language)
    {
        $this->language = $language;
    }

    /**
     * @return TranslationsDatabase
     */
    public function getDatabase()
    {
        return $this->database;
    }

    /**
     * @param TranslationsDatabase $database
     */
    public function setDatabase($database)
    {
        $this->database = $database;
    }


    /**
     * Parses a string and returns translatable items
     * @param $input
     * @return array
     */
    public function findTranslatables($input)
    {
        /**
         * matches[0] is full translation markup
         * matches[1] is translation keys
         * matches[2] is default stanza
         * matches[3] is default content
         */
        preg_match_all('/__{{(.*?)(,\s*[\'"](.*?)[\'"])?}}/', $input, $matches);

        $translatables = [];

        foreach ($matches[0] as $index => $match) {
            $translatables[] = [
                'replace' => $match,
                'key' => $matches[1][$index],
                'default' => $matches[3][$index]
            ];
        }

        return $translatables;
    }

    /**
     * @param $input
     * @return mixed
     */
    public function translateString($input)
    {

        $translatables = $this->findTranslatables($input);
        $output = $input;

        foreach ($translatables as $translatable) {

            $translation = $this->getDatabase()->findTranslation($this->getLanguage(), $translatable['key']);
            $replacement = '';

            // Found a translation entry, so translate it
            if ($translation) {
                $replacement = $translation->getContent();
            }
            // No translation entry, but the translation markup had a default
            elseif (isset($translatable['default']) && $translatable['default']) {
                $replacement = $translatable['default'];
            }

            $output = str_replace($translatable['replace'], $replacement, $output);
        }

        return $output;
    }

    /**
     * @param array $input
     * @return mixed
     */
    public function translateArray(array $input)
    {

        foreach ($input as $key => $value) {

            if (is_string($value)) {
                $input[$key] = $this->translateString($value);
            }
            elseif (is_array($value)) {
                $input[$key] = $this->translateArray($value);
            }

        }

        return $input;

    }
}