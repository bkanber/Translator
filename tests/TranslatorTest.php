<?php

namespace bkanber\Translator\Tests;

use bkanber\Translator\TranslationsDatabase;
use bkanber\Translator\Translator;

class TranslatorTest extends \PHPUnit_Framework_TestCase
{

    /** @var TranslationsDatabase */
    public static $database;

    public static function setUpBeforeClass()
    {
        $dbFileContents = file_get_contents(realpath(__DIR__ . '/translations.database'));
        $dbDefinitions = json_decode($dbFileContents, true);
        self::$database = TranslationsDatabase::createFromArray($dbDefinitions);
    }

    public function testFindsTranslatables()
    {
        $html = file_get_contents(realpath(__DIR__ . '/translatable-html.html'));
        $translator = new Translator('en', self::$database);
        $translatables = $translator->findTranslatables($html);

        self::assertCount(6, $translatables);
        self::assertEquals('page_title', $translatables[0]['key']);


    }

    public function testTranslatesHtml()
    {
        $html = file_get_contents(realpath(__DIR__ . '/translatable-html.html'));
        $translator = new Translator('en', self::$database);
        $translated = $translator->translateString($html);

        // Translated into english
        self::assertTrue(false !== strpos($translated, '<title>English Page Title</title>'));
        self::assertTrue(false !== strpos($translated, '<h1 class="title-en">'));
        // This tag has a spanish translation but uses default for english:
        self::assertTrue(false !== strpos($translated, '<p>Hello, welcome to my webpage.</p>'));
        self::assertTrue(false !== strpos($translated, '<img src="https://example.com/image-en.jpg" />'));
        self::assertTrue(false !== strpos($translated, '<div class="default-only">Default Only!</div>'));
        self::assertTrue(false !== strpos($translated, '<div class="no-default">No Default English</div>'));



        // Now translate into spanish
        $translator = new Translator('es', self::$database);
        $translated = $translator->translateString($html);
        self::assertTrue(false !== strpos($translated, '<title>Spanish Page Title</title>'));
        self::assertTrue(false !== strpos($translated, '<h1 class="title-es">'));
        self::assertTrue(false !== strpos($translated, '<p>Intro in Spanish</p>'));
        self::assertTrue(false !== strpos($translated, '<img src="https://example.com/image-es.jpg" />'));
        self::assertTrue(false !== strpos($translated, '<div class="default-only">Default Only!</div>'));
        self::assertTrue(false !== strpos($translated, '<div class="no-default">No Default Spanish</div>'));

    }

    public function testTranslatesArray()
    {
        $raw = file_get_contents(realpath(__DIR__ . '/translatable-json.json'));
        $json = json_decode($raw, true);

        $translator = new Translator('en', self::$database);
        $english = $translator->translateArray($json);

        self::assertEquals('There was an error', $english['meta']['error']);
        self::assertEquals('Item description goes here', $english['data']['items'][0]['desc']);
        self::assertEquals('Default Child', $english['data']['items'][1]['children'][0]['value']);
        self::assertEquals('deep nested in english', $english['data']['items'][1]['children'][0]['subobject']['middle']['end']);

        $translator = new Translator('es', self::$database);
        $spanish = $translator->translateArray($json);

        self::assertEquals('Uno error por favor', $spanish['meta']['error']);
        self::assertEquals('Item description en espanol', $spanish['data']['items'][0]['desc']);
        self::assertEquals('la palapa', $spanish['data']['items'][1]['children'][0]['value']);
        self::assertEquals('deep nested in spanish', $spanish['data']['items'][1]['children'][0]['subobject']['middle']['end']);
    }

}
