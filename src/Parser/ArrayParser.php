<?php


namespace bkanber\Translator\Parser;


use bkanber\Translator\Exception\MalformedInputException;
use bkanber\Translator\Translatable;
use bkanber\Translator\Translation;

/**
 * Class ArrayParser
 * @package bkanber\Translator\Parser
 */
class ArrayParser extends AbstractParser implements ParserInterface
{

    /**
     * Parse the input and generate Translatables.
     * @return Translatable[]
     * @throws MalformedInputException
     */
    public function parse()
    {
        $input = $this->getInput();

        if (!is_array($input)) {
            throw new MalformedInputException("ArrayParser requires an array as input");
        }

        $stringParser = new StringParser();
        $arrayParser = new self();
        $translatables = [];

        foreach ($input as $key => $value) {

            $theseTranslatables = [];

            if (is_string($value)) {
                $stringParser->setInput($value);
                $theseTranslatables = $stringParser->parse();
            } elseif (is_array($value)) {
                $arrayParser->setInput($value);
                $theseTranslatables = $arrayParser->parse();
            }

            $translatables = array_merge($translatables, $theseTranslatables);

        }

        $this->setTranslatables($translatables);
        return $this->getTranslatables();

    }

    /**
     * Replace the translation keys in the input with the provided Translations
     * @param Translation[] $translations
     * @return mixed
     * @throws MalformedInputException
     */
    public function replace($translations)
    {
        $output = $this->input;

        if (!is_array($output)) {
            throw new MalformedInputException("ArrayParser requires an array as input");
        }

        $stringParser = new StringParser();
        $arrayParser = new self();

        foreach ($output as $key => $value) {

            if (is_string($value)) {
                $stringParser->setInput($value);
                $stringParser->parse();
                $output[$key] = $stringParser->replace($translations);
            } elseif (is_array($value)) {
                $arrayParser->setInput($value);
                $arrayParser->parse();
                $output[$key] = $arrayParser->replace($translations);
            }
        }

        return $output;
    }
}
