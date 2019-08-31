<?php

namespace bkanber\Translator\Tests\Parser;

use bkanber\Translator\Parser\StringParser;
use bkanber\Translator\Translation;

/**
 * Class StringParserTest
 * @package bkanber\Translator\Tests\Parser
 */
class StringParserTest extends \PHPUnit_Framework_TestCase
{

    public function testDoesGetAndSetInput()
    {
        $input = 'hello world!';
        $parser = (new StringParser())->setInput($input);

        self::assertEquals($input, $parser->getInput());

        $parser->setInput('new input');
        self::assertEquals('new input', $parser->getInput());
    }

    public function testParsesTranslatables()
    {
        $input = '__{{key1}} __{{key2}} __{{keyWithDefault,"default goes here"}}';
        $parser = (new StringParser())->setInput($input);

        $translatables = $parser->parse();

        self::assertCount(3, $translatables);

        self::assertEquals('__{{key1}}', $translatables[0]->getReplace());
        self::assertEquals('key1', $translatables[0]->getKey());
        self::assertEquals('', $translatables[0]->getDefault());

        self::assertEquals('__{{key2}}', $translatables[1]->getReplace());
        self::assertEquals('key2', $translatables[1]->getKey());
        self::assertEquals('', $translatables[1]->getDefault());

        self::assertEquals('__{{keyWithDefault,"default goes here"}}', $translatables[2]->getReplace());
        self::assertEquals('keyWithDefault', $translatables[2]->getKey());
        self::assertEquals('default goes here', $translatables[2]->getDefault());
    }

    public function testGetTranslatableKeysDoesDedupe()
    {
        $input = <<<EOSTRING
<p>Hello, __{{person_title, "Friend"}}</p>
<p>The weather is __{{weather}}</p>
<p>And you are __{{person_title}}</p>
EOSTRING;

        $parser = (new StringParser())->setInput($input);
        $translatables = $parser->parse();

        self::assertCount(3, $translatables);

        $keys = $parser->getTranslatableKeys();
        self::assertCount(2, $keys);
        self::assertEquals(['person_title', 'weather'], $keys);

    }

    public function testMakesReplacements()
    {
        $input = <<<EOSTRING
<p>Hello, __{{person_title, "Friend"}}</p>
<p>The weather is __{{weather}}</p>
<p>And you are __{{person_title}}</p>
<div><img src="__{{image1,'https://burakkanber.com/image.jpg'}}" /></div>
EOSTRING;
        $translations = [
            Translation::createFromArray(
                ['key' =>'person_title', 'content' => 'Burak', 'locale' => 'test']
            ),
            Translation::createFromArray(
                ['key' =>'weather', 'content' => 'frightful', 'locale' => 'test']
            ),
        ];
        $parser = (new StringParser())->setInput($input);
        $parser->parse();
        $replaced = $parser->replace($translations);

        self::assertTrue(false !== strpos($replaced, '<p>Hello, Burak</p>'));
        self::assertTrue(false !== strpos($replaced, '<p>And you are Burak</p>'));
        self::assertTrue(false !== strpos($replaced, '<p>The weather is frightful</p>'));
        self::assertTrue(false !== strpos($replaced, '<img src="https://burakkanber.com/image.jpg" />'));

    }
}
