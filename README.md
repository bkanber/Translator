# Translations Library
For Splash, by Burak

## Overview

You can translate strings or arrays. String Example:

```php

// Create a driver that provides translations (see also PdoDriver for database functionality)
$driver = ArrayDriver::createFromArray([
    ['domain' => 'cust1', 'locale' => 'en', 'key' => 'greeting', 'content' => 'Hello'],
    ['domain' => 'cust1', 'locale' => 'es', 'key' => 'greeting', 'content' => 'Hola']
]);

$enTranslator = new Translator($driver);
$enTranslator->setLocale('en')->setDomain('cust1');
$inEnglish = $enTranslator->translateString("__{{greeting}}, my friend!");
// $inEnglish === 'Hello, my friend!'

$esTranslator = new Translator($driver);
$esTranslator->setLocale('es')->setDomain('cust1');
$inSpanish = $esTranslator->translateString("__{{greeting}}, my friend!");
// $inSpanish === 'Hola, my friend!'

// Translation tags can also have defaults if the key or language isn't found:
$deTranslator = new Translator($db);
$deTranslator->setLocale('de')->setDomain('cust1');
$withDefault = $deTranslator->translateString("__{{greeting, 'Greetings'}}, my friend!");
// $withDefault === 'Greetings, my friend!'

```

You can also recursively translate PHP arrays:

```php

$driver = ArrayDriver::createFromArray([
    ['locale' => 'en', 'key' => 'greeting', 'content' => 'Hello'],
    ['locale' => 'es', 'key' => 'greeting', 'content' => 'Hola']
]);

$translator = new Translator($driver);
$translated = $translator->setLocale('en')->translateArray([
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

## Translations and Template Tags

Translations (modeled by the `Translation` class) have four properties: a locale, a key, a domain, and content. The locale is a custom string (i.e., it does not need to be an ISO locale code) that represents the language, dialect, or flavor that you wish to translate to. The domain is another custom string that helps you distinguish translation catalogs by (for example) a customer ID or other application domain. For example, in a multi-tentant SaaS application, the domain might represent a customer ID. The key of a translation refers to the template tag that should be used to represent it, and the translation content is what the key will be replaced with.

Template tags are intended to appear in strings with the format `__{{key, "Default text"}}`. The default text is optional and can be wrapped in either single quotes or double quotes. The key is required. Unlike other translation systems, the domain is not part of the key in the template tag. Therefore, a tag like `__{{greeting}}` could have a corresponding translation of "Hello, Splash Customer" or "Hello, Android User", depending on the domain set on the translator.

## Installation

First, `composer require` this library.

If you wish to store translations in your database, you must create the `translations` table with the appropriate columns. If you are already using the `phinx` migration library, you can copy the phinx migration from the `dist/migrations` folder in this repo. If you are using another migration library, create a database table called `translations` with the following columns:

- key; varchar or text, not null
- locale; varchar or text, not null (if you know you're exclusively using ISO locale codes you can make this field varchar(5))
- domain; varchar or text, nullable
- content; text, not null

In your application you will need to at very least instantiate the Translator class and attach a driver to use for translation lookups. 

## Connecting to a Database

Instantiate the Translator class with an instance of `PdoDriver` instead of `ArrayDriver`.

You will need a table called 'translations' with the text fields 'key', 'locale', 'domain', and 'content'. 
It is a good idea to index on (key,locale,domain). There is an example Phinx migration in the `dist/migrations` folder.

```php
$pdo = new \PDO('mysql::.....', 'user', 'pass');
$driver = new PdoDriver($pdo);
$translator = new Translator($driver);
```

You can manage the translations in the database by interacting with the driver directly:

```php
$pdo = new \PDO('mysql::.....', 'user', 'pass');
$driver = new PdoDriver($pdo);

$driver->upsertTranslation('en', 'greeting', 'customer1', 'Hello');
$driver->deleteTranslation('en', 'greeting', 'customer1');
$driver->createTranslation('en', 'greeting', 'customer1', 'Hello');
$driver->updateTranslation('en', 'greeting', 'customer1', 'Hi');

```

## Singleton Instance

The Translator class provides a singleton/instance manager so that you can use Translator instances globally.

Set up: 

```php
Translator::instance()->setDriver(new PdoDriver(new \PDO( ... )));
```

Use elsewhere:

```php
Translator::instance()->setLocale('en')->translateString('__{{name_tag}}');
```

You can also manage multiple, named instances: 

```php
Translator::instance()->setDriver(new PdoDriver(new \PDO( ... )));
Translator::instance('temp')->setDriver(ArrayDriver::createFromArray( ... ));

Translator::instance('temp')->setLocale('en')->transArray(...);
```

## Using Custom Drivers

If you need to connect to a translation source that neither ArrayDriver nor PdoDriver can manage, you can create your own.
Simply implement the DriverInterface class in a custom class of your own and use that.

```php
class MyCustomDriver implements DriverInterface {
    public function __construct($someCustomResource) { ... }
    public function findTranslation($locale, $key, $domain = null) { ... }
    public function findTranslations($locale, $keys, $domain = null) { ... }
    public function updateTranslation($locale, $key, $domain, $content) { ... }
    public function createTranslation($locale, $key, $domain, $content) { ... }
    public function upsertTranslation($locale, $key, $domain, $content) { ... }
    public function deleteTranslation($locale, $key, $domain = null) { ... }
}

$myCustomResource = new CustomResource(...);
$translator = new Translator(new MyCustomDriver($myCustomResource));
```

## Using Custom Parsers

This library is shipped with a `StringParser` and an `ArrayParser`, with translation methods aliased by `translateString` and `translateArray`. However you can create a custom Parser class to deal with other types of inputs. 

Create a class that implements the ParserInterface (and potentially extends AbstractParser) and send an instance of it to `translateCustom`:

```php
class MyCustomParser extends AbstractParser implements ParserInterface {
    public function parse() {
        // Must return an array of Translatable objects
    } 
    public function replace($translations) {
        // Must replace the Translatables in the input with the $translations given, and return the translated output
    }
}
```

You can then use it like so: 

```php
$translator = new Translator($driver);

$output = $translator->translateCustom($input, new MyCustomParser());
```

## Using a Missing Translation Handler

This library provides a mechanism for alerting you when a translation key with no matching translation is found. This is helpful for putting together a list of translations that still need to be written.

The LogFileHandler accepts a PHP file resource and will write TSV content to it. You should open a file with append mode to append to the log.

```php
$logFile = fopen($filePath, 'a');
$handler = new LogFileHandler($logFile);
$translator->setHandler($handler);

// Missing translation keys will get logged to your $logFile here:
$translator->translateString(...);
```

You can also create a custom handler (to, for instance, log missing translations to a DB, slack, elasticsearch, etc). Simply implement the HandlerInterface.


## Running the tests

```
$ composer install
$ vendor/bin/phpunit tests/
```

