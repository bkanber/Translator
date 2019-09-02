<?php

namespace bkanber\Translator\Tests\Handler;

use bkanber\Translator\Driver\ArrayDriver;
use bkanber\Translator\Handler\LogFileHandler;
use bkanber\Translator\Translator;

class LogFileHandlerTest extends \PHPUnit_Framework_TestCase
{
    public function testHandlesMissingTranslations()
    {
        $driver = ArrayDriver::createFromArray([
            ['locale' => 'en', 'key' => 'name', 'content' => 'Burak', 'domain' => 'cust1'],
        ]);

        $file = fopen('php://temp', 'a+');
        $handler = new LogFileHandler($file);

        $translator = new Translator($driver);
        $translator->setHandler($handler);


        $output = $translator
            ->setDomain('cust1')
            ->setLocale('en')
            ->translateString('__{{name}} __{{surname}} __{{withDefault, "Hi"}}');


        self::assertEquals('Burak  Hi', $output);

        rewind($file);
        $log = stream_get_contents($file);
        $logRows = explode("\n", trim($log));

        self::assertEquals(2, count($logRows));

        $item = explode("\t", $logRows[0]);
        self::assertEquals('cust1', $item[1]);
        self::assertEquals('en', $item[2]);
        self::assertEquals('surname', $item[3]);
        self::assertEquals('', $item[4]);
        self::assertEquals('__{{surname}}', $item[5]);

        $item = explode("\t", $logRows[1]);
        self::assertEquals('cust1', $item[1]);
        self::assertEquals('en', $item[2]);
        self::assertEquals('withDefault', $item[3]);
        self::assertEquals('Hi', $item[4]);
        self::assertEquals('__{{withDefault, "Hi"}}', $item[5]);

        fclose($file);
    }

}
