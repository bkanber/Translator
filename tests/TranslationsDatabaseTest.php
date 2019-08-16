<?php

namespace bkanber\Translator\Tests;

use bkanber\Translator\Translation;
use bkanber\Translator\TranslationsDatabase;

class TranslationsDatabaseTest extends \PHPUnit_Framework_TestCase
{

    public function testLoadsAndFindsTranslations()
    {
        $database = TranslationsDatabase::createFromArray([
            ['language' => 'en', 'key' => 'greeting', 'content' => 'hello'],
            ['language' => 'es', 'key' => 'greeting', 'content' => 'hola'],
        ]);

        $translation = $database->findTranslation('en', 'greeting');
        self::assertInstanceOf(Translation::class, $translation);
        self::assertEquals('hello', $translation->getContent());

        $translation = $database->findTranslation('es', 'greeting');
        self::assertInstanceOf(Translation::class, $translation);
        self::assertEquals('hola', $translation->getContent());

        $translation = $database->findTranslation('de', 'greeting');
        self::assertNull($translation);

        $translation = $database->findTranslation('en', 'missing');
        self::assertNull($translation);
    }

}
