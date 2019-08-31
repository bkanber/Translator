<?php

namespace bkanber\Translator\Tests\Driver;

use bkanber\Translator\Driver\ArrayDriver;
use bkanber\Translator\Translation;

/**
 * Class ArrayDriverTest
 * @package bkanber\Translator\Tests\Driver
 */
class ArrayDriverTest extends \PHPUnit_Framework_TestCase
{

    /** @var ArrayDriver */
    protected $driver;

    public function setUp()
    {
        $this->driver = ArrayDriver::createFromArray(
            [
                ['domain' => 'cust1', 'locale' => 'en', 'key' => 'title', 'content' => 'Customer One'],
                ['domain' => 'cust1', 'locale' => 'sp', 'key' => 'title', 'content' => 'Customer Uno'],

                ['domain' => 'cust2', 'locale' => 'en', 'key' => 'title', 'content' => 'Customer Two'],
                ['domain' => 'cust2', 'locale' => 'sp', 'key' => 'title', 'content' => 'Customer Dos'],
                ['domain' => 'cust2', 'locale' => 'sp', 'key' => 'nombre', 'content' => 'Customer Nombre'],
                ['domain' => 'cust2', 'locale' => 'sp', 'key' => 'preguntas', 'content' => 'Uno Preguntas'],

                ['domain' => 'test', 'locale' => 'en', 'key' => 'delete_me', 'content' => 'To Be Deleted'],
                ['domain' => 'test', 'locale' => 'en', 'key' => 'update_me', 'content' => 'To Be Updated'],
                ['domain' => 'test', 'locale' => 'en', 'key' => 'upsert_me', 'content' => 'To Be Upserted'],
            ]
        );
    }

    public function tearDown()
    {
        unset($this->driver);
    }

    public function testUpdateTranslation()
    {
        $before = $this->driver->findTranslation('en', 'update_me', 'test');
        self::assertNotNull($before);
        self::assertInstanceOf(Translation::class, $before);
        self::assertEquals('To Be Updated', $before->getContent());

        $updated = $this->driver->updateTranslation('en', 'update_me', 'test', 'I was Updated');
        self::assertInstanceOf(Translation::class, $updated);
        self::assertEquals('I was Updated', $updated->getContent());

        $found = $this->driver->findTranslation('en', 'update_me', 'test');
        self::assertNotNull($found);
        self::assertInstanceOf(Translation::class, $found);
        self::assertEquals('I was Updated', $found->getContent());

    }

    public function testUpsertTranslation()
    {
        self::assertCount(9, $this->driver->getTranslations());

        // Updates existing
        $this->driver->upsertTranslation('en', 'upsert_me', 'test', 'I was Updated/Upserted');
        $updated = $this->driver->findTranslation('en', 'upsert_me', 'test');
        self::assertNotNull($updated);
        self::assertEquals('I was Updated/Upserted', $updated->getContent());

        // Creates new
        $this->driver->upsertTranslation('en', 'new_upsert', 'test', 'I was created by upsert');
        $created = $this->driver->findTranslation('en', 'new_upsert', 'test');
        self::assertNotNull($created);
        self::assertEquals('en', $created->getLocale());
        self::assertEquals('test', $created->getDomain());
        self::assertEquals('new_upsert', $created->getKey());
        self::assertEquals('I was created by upsert', $created->getContent());

    }

    public function testCreateFromArray()
    {
        self::assertCount(9, $this->driver->getTranslations());
    }

    public function testDeleteTranslation()
    {
        self::assertCount(9, $this->driver->getTranslations());
        $found = $this->driver->findTranslation('en', 'delete_me', 'test');
        self::assertNotNull($found);
        self::assertEquals('To Be Deleted', $found->getContent());

        $return = $this->driver->deleteTranslation('en', 'delete_me', 'test');
        self::assertTrue($return);
        self::assertCount(8, $this->driver->getTranslations());
        self::assertNull($this->driver->findTranslation('en', 'delete_me', 'test'));

        // Now try deleting something that doesn't exist
        $shouldBeFalse = $this->driver->deleteTranslation('en', 'garbage', 'test');
        self::assertFalse($shouldBeFalse);
        self::assertCount(8, $this->driver->getTranslations());

    }

    public function testFindTranslation()
    {

        $notFound = $this->driver->findTranslation('fr', 'title', 'cust1');
        self::assertNull($notFound);

        $found = $this->driver->findTranslation('sp', 'title', 'cust2');
        self::assertNotNull($found);
        self::assertInstanceOf(Translation::class, $found);
        self::assertEquals('Customer Dos', $found->getContent());

    }

    public function testFindTranslations()
    {
        // 'delete_me' should not be found because it is not part of 'cust2' domain or 'sp' locale

        $translations = $this->driver->findTranslations('sp', ['title', 'nombre', 'preguntas', 'delete_me'], 'cust2');
        self::assertCount(3, $translations);

    }

    public function testCreateTranslation()
    {
        $countBefore = count($this->driver->getTranslations());
        $translation = $this->driver->createTranslation('en', 'created', 'cust1', 'I was just created');
        self::assertInstanceOf(Translation::class, $translation);
        self::assertEquals($countBefore + 1, count($this->driver->getTranslations()));
        $found = $this->driver->findTranslation('en', 'created', 'cust1');
        self::assertInstanceOf(Translation::class, $found);
        self::assertEquals('I was just created', $found->getContent());
        self::assertEquals('cust1', $found->getDomain());
        self::assertEquals('created', $found->getKey());
        self::assertEquals('en', $found->getLocale());

    }

}
