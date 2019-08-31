<?php

namespace bkanber\Translator\Tests;

use bkanber\Translator\Tests\Driver\AbstractPdoDriverTest;
use bkanber\Translator\Translator;

/**
 * Class TranslatorTest
 * @package bkanber\Translator\Tests
 */
class TranslatorTest extends AbstractPdoDriverTest
{

    /** @var Translator */
    public static $translator;

    public static function setUpBeforeClass()
    {
        parent::setUpBeforeClass();

        $dbFileContents = file_get_contents(realpath(__DIR__ . '/translations.database'));
        $dbDefinitions = json_decode($dbFileContents, true);

        self::seedFromArray($dbDefinitions);

        self::$translator = new Translator(self::$driver);
    }

    public function setUp()
    {
        parent::setUp();
        self::$translator->setDomain(null)->setLocale(null);
    }

    public function testTranslatesHtml()
    {
        $html = file_get_contents(realpath(__DIR__ . '/translatable-html.html'));

        $translated = self::$translator->setLocale('en')->translateString($html);

        // Translated into english
        self::assertTrue(false !== strpos($translated, '<title>English Page Title</title>'));
        self::assertTrue(false !== strpos($translated, '<h1 class="title-en">'));
        // This tag has a spanish translation but uses default for english:
        self::assertTrue(false !== strpos($translated, '<p>Hello, welcome to my webpage.</p>'));
        self::assertTrue(false !== strpos($translated, '<img src="https://example.com/image-en.jpg" />'));
        self::assertTrue(false !== strpos($translated, '<div class="default-only">Default Only!</div>'));
        self::assertTrue(false !== strpos($translated, '<div class="no-default">No Default English</div>'));



        // Now translate into spanish
        $translated = self::$translator->setLocale('es')->translateString($html);

        self::assertTrue(false !== strpos($translated, '<title>Spanish Page Title</title>'));
        self::assertTrue(false !== strpos($translated, '<h1 class="title-es">'));
        self::assertTrue(false !== strpos($translated, '<p>Intro in Spanish</p>'));
        self::assertTrue(false !== strpos($translated, '<img src="https://example.com/image-es.jpg" />'));
        self::assertTrue(false !== strpos($translated, '<div class="default-only">Default Only!</div>'));
        self::assertTrue(false !== strpos($translated, '<div class="no-default">No Default Spanish</div>'));

        // Now do english with a specific domain
        $translated = self::$translator->setLocale('en')->setDomain('cust1')->translateString($html);
        self::assertTrue(false !== strpos($translated, '<title>English Page Title for Cust1</title>'));
        self::assertTrue(false !== strpos($translated, '<h1 class="title-en-cust1">'));
        self::assertTrue(false !== strpos($translated, '<p>Hello, welcome to my webpage.</p>'));
        self::assertTrue(false !== strpos($translated, '<img src="https://example.com/image-en-cust1.jpg" />'));
        self::assertTrue(false !== strpos($translated, '<div class="default-only">Default Only!</div>'));
        self::assertTrue(false !== strpos($translated, '<div class="no-default">No Default English For Cust 1</div>'));

        // Now do spanish with a specific domain
        $translated = self::$translator->setLocale('es')->setDomain('cust1')->translateString($html);
        self::assertTrue(false !== strpos($translated, '<title>Spanish Page Title for Cust1</title>'));
        self::assertTrue(false !== strpos($translated, '<h1 class="title-es-cust1">'));
        self::assertTrue(false !== strpos($translated, '<p>Intro in Spanish For Cust 1</p>'));
        self::assertTrue(false !== strpos($translated, '<img src="https://example.com/image-es-cust1.jpg" />'));
        self::assertTrue(false !== strpos($translated, '<div class="default-only">Default Only!</div>'));
        self::assertTrue(false !== strpos($translated, '<div class="no-default">No Default Spanish For Cust 1</div>'));
    }

    public function testTranslatesArray()
    {
        $raw = file_get_contents(realpath(__DIR__ . '/translatable-json.json'));
        $json = json_decode($raw, true);

        self::$translator->setLocale('en');
        $english = self::$translator->translateArray($json);

        self::assertEquals('There was an error', $english['meta']['error']);
        self::assertEquals('Item description goes here', $english['data']['items'][0]['desc']);
        self::assertEquals('Default Child', $english['data']['items'][1]['children'][0]['value']);
        self::assertEquals('deep nested in english', $english['data']['items'][1]['children'][0]['subobject']['middle']['end']);

        self::$translator->setLocale('es');
        $spanish = self::$translator->translateArray($json);

        self::assertEquals('Uno error por favor', $spanish['meta']['error']);
        self::assertEquals('Item description en espanol', $spanish['data']['items'][0]['desc']);
        self::assertEquals('la palapa', $spanish['data']['items'][1]['children'][0]['value']);
        self::assertEquals('deep nested in spanish', $spanish['data']['items'][1]['children'][0]['subobject']['middle']['end']);

        // Now do everything again with cust1 domain
        $english = self::$translator->setLocale('en')->setDomain('cust1')->translateArray($json);

        self::assertEquals('There was an error For Cust 1', $english['meta']['error']);
        self::assertEquals('Item description goes here For Cust 1', $english['data']['items'][0]['desc']);
        self::assertEquals('Default Child', $english['data']['items'][1]['children'][0]['value']);
        self::assertEquals('deep nested in english For Cust 1', $english['data']['items'][1]['children'][0]['subobject']['middle']['end']);

        $spanish = self::$translator->setLocale('es')->setDomain('cust1')->translateArray($json);

        self::assertEquals('Uno error por favor For Cust 1', $spanish['meta']['error']);
        self::assertEquals('Item description en espanol For Cust 1', $spanish['data']['items'][0]['desc']);
        self::assertEquals('la palapa For Cust 1', $spanish['data']['items'][1]['children'][0]['value']);
        self::assertEquals('deep nested in spanish For Cust 1', $spanish['data']['items'][1]['children'][0]['subobject']['middle']['end']);
    }

    public function testSingletonInstances()
    {
        $t1 = Translator::instance()->setDriver(self::$driver);
        $dupe = Translator::instance();

        self::assertEquals($t1, $dupe);

        $t2 = Translator::instance('otherdb');
        self::assertNotEquals($t2, $t1);
    }

    /**
     * @expectedException \bkanber\Translator\Exception\DriverMissingException
     */
    public function testFailsIfNoDriverConfigured()
    {
        $translator = new Translator();
        $translator->translateString('hello world');
    }
}
