# Translations Demo
For Splash, by Burak

This repo demonstrates a simple database-backed translations system. The TranslationsDatabase class mocks a database connection and a method for finding a Translation object. The Translation class mocks a DB ORM record. The Translator class is the main interface to the translator.

You can translate strings or arrays. String Example:

```php

// Normally, TranslationsDatabase would be an actual DB connection to a translations table or similar
$db = TranslationsDatabase::createFromArray([
    ['language' => 'en', 'key' => 'greeting', 'content' => 'Hello'],
    ['language' => 'es', 'key' => 'greeting', 'content' => 'Hola']
]);

$enTranslator = new Translator('en', $db);
$inEnglish = $enTranslator->translateString("__{{greeting}}, my friend!");
// $inEnglish === 'Hello, my friend!'

$esTranslator = new Translator('en', $db);
$inSpanish = $esTranslator->translateString("__{{greeting}}, my friend!");
// $inSpanish === 'Hola, my friend!'

// Translation tags can also have defaults if the key or language isn't found:
$deTranslator = new Translator('de', $db);
$withDefault = $deTranslator->translateString("__{{greeting, 'Greetings'}}, my friend!");
// $withDefault === 'Greetings, my friend!'

```

You can also recursively translate PHP arrays:

```php

$db = TranslationsDatabase::createFromArray([
    ['language' => 'en', 'key' => 'greeting', 'content' => 'Hello'],
    ['language' => 'es', 'key' => 'greeting', 'content' => 'Hola']
]);

$translator = new Translator('en', $db);
$translated = $translator->translateArray([
    "data" => [
        "items" => [
            [
                "content" => [
                    "tagName" => "h1",
                    "value" => "__{{greeting, 'Greetings'}}"
                ]
            ]
        ]
    ]
]);

// $translated['data']['items'][0]['content']['value'] === "Hello"

```

## Running the tests

```
$ composer install
$ vendor/bin/phpunit tests/
```

