<?php


namespace bkanber\Translator\Parser;

use bkanber\Translator\Parser\ParserInterface;
use bkanber\Translator\Translatable;
use bkanber\Translator\Translation;

/**
 * Class Parser
 * @package bkanber\Translator
 */
class StringParser extends AbstractParser implements ParserInterface
{

    /**
     * @return Translatable[]
     */
    public function parse()
    {

        /**
         * matches[0] is full translation markup
         * matches[1] is translation keys
         * matches[2] is default stanza
         * matches[3] is default content
         */
        preg_match_all('/__{{(.*?)(,\s*[\'"](.*?)[\'"])?}}/', $this->getInput(), $matches);

        $translatables = [];

        foreach ($matches[0] as $index => $match) {
            $translatables[] = new Translatable($match, $matches[1][$index], $matches[3][$index]);
        }

        $this->setTranslatables($translatables);

        return $this->getTranslatables();
    }

    /**
     * @param Translation[] $translations
     * @return string
     */
    public function replace($translations)
    {
        $output = $this->input;
        $this->setTranslations($translations);

        foreach ($this->getTranslatables() as $translatable) {

            $translation = $this->findTranslation($translatable->getKey());
            $replacement = '';
            $default = $translatable->getDefault();
            $replace = $translatable->getReplace();

            // Found a translation entry, so translate it
            if ($translation) {
                $replacement = $translation->getContent();
            }
            // No translation entry, but the translation markup had a default
            elseif (isset($default) && $default) {
                $replacement = $default;
            }

            $output = str_replace($replace, $replacement, $output);
        }

        return $output;
    }
}
