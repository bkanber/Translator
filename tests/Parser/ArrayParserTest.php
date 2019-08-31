<?php

namespace bkanber\Translator\Tests\Parser;

use bkanber\Translator\Parser\ArrayParser;
use bkanber\Translator\Translation;

class ArrayParserTest extends \PHPUnit_Framework_TestCase
{

    /** @var ArrayParser */
    protected $parser;

    public function setUp()
    {
        $this->parser = new ArrayParser();
        $this->parser->setInput([
            [
                'key' => 'meta',
                'content' => [
                    'error' => '__{{err_msg_generic, "An unknown error occurred."}}',
                    'status' => '__{{status_code_err}}'
                ]
            ],
            [
                'key' => 'data',
                'content' => [
                    'items' => [
                        [
                            'name' => '__{{keyed_name}}'
                        ],
                        [
                            'name' => '__{{keyed_name}}',
                            'meta' => [
                                'nested' => [
                                    'child' => '__{{nested_child, "Nested child value"}}',
                                    'prepended' => 'This is prepended with: __{{prepended}}'
                                ]
                            ]
                        ]

                    ]
                ]

            ]
        ]);
    }

    public function testParse()
    {

        $translatables = $this->parser->parse();
        self::assertCount(6, $translatables);
        self::assertEquals([
            'err_msg_generic',
            'status_code_err',
            'keyed_name',
            'nested_child',
            'prepended'
        ], $this->parser->getTranslatableKeys());

        self::assertEquals('An unknown error occurred.', $translatables[0]->getDefault());
        self::assertEquals('', $translatables[1]->getDefault());

    }

    public function testReplace()
    {
        $translations = [
            new Translation('', 'status_code_err', 'STATUS_ERR'),
            new Translation('', 'keyed_name', 'Bobby Jones'),
            new Translation('', 'nested_child', 'Child of The Sun'),
            new Translation('', 'prepended', 'Prependio'),
        ];

        $this->parser->parse();
        $translated = $this->parser->replace($translations);

        // Untouched
        self::assertEquals('meta', $translated[0]['key']);
        // Used default
        self::assertEquals('An unknown error occurred.', $translated[0]['content']['error']);
        // Translated
        self::assertEquals('STATUS_ERR', $translated[0]['content']['status']);
        // Translated twice
        self::assertEquals('Bobby Jones', $translated[1]['content']['items'][0]['name']);
        self::assertEquals('Bobby Jones', $translated[1]['content']['items'][1]['name']);
        // Deeply nested
        self::assertEquals('Child of The Sun', $translated[1]['content']['items'][1]['meta']['nested']['child']);
        // Translation Mixed with literal string
        self::assertEquals('This is prepended with: Prependio', $translated[1]['content']['items'][1]['meta']['nested']['prepended']);

    }

    /**
     * @expectedException \bkanber\Translator\Exception\MalformedInputException
     */
    public function testFailsIfMalformedInputGiven()
    {
        $this->parser->setInput('hello world')->parse();
    }
}
