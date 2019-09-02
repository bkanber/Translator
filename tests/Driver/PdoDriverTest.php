<?php

namespace bkanber\Translator\Tests\Driver;

use bkanber\Translator\Driver\PdoDriver;
use bkanber\Translator\Translation;
use bkanber\Translator\Translator;

/**
 * Class PdoDriverTest
 * @package bkanber\Translator\Tests\Driver
 */
class PdoDriverTest extends AbstractPdoDriverTest
{

    public function tearDown()
    {
        $this->truncate();
    }

    public function testCreateTranslation()
    {
        self::assertEquals(0, $this->countRows());
        $translation = self::$driver->createTranslation('en', 'test', null, 'some content');
        self::assertEquals(1, $this->countRows());
        self::assertEquals('some content', $translation->getContent());
        $this->truncate();
        self::assertEquals(0, $this->countRows());
    }

    public function testUpdateTranslation()
    {
        self::seedFromArray([
            ['domain' => null, 'locale' => 'test', 'key' => 'update', 'content' => 'Needs Update'],
            ['domain' => 'd1', 'locale' => 'test', 'key' => 'update', 'content' => 'Needs Update'],
            ['domain' => null, 'locale' => 'untouched', 'key' => 'update', 'content' => 'Needs Update'],
            ['domain' => 'd1', 'locale' => 'untouched', 'key' => 'update', 'content' => 'Needs Update'],
        ]);

        self::assertEquals(4, $this->countRows());

        self::$driver->updateTranslation('test', 'update', null, 'Content for null domain');
        $updated = self::$driver->findTranslation('test', 'update', null);
        self::assertEquals('Content for null domain', $updated->getContent());
        $notUpdated = self::$driver->findTranslation('test', 'update', 'd1');
        self::assertEquals('Needs Update', $notUpdated->getContent());

    }

    public function testUpsertTranslation()
    {
        self::seedFromArray([
            ['domain' => null, 'locale' => 'test', 'key' => 'upsert', 'content' => 'Needs Upsert'],
            ['domain' => 'd1', 'locale' => 'test', 'key' => 'upsert', 'content' => 'Needs Upsert'],
            ['domain' => null, 'locale' => 'untouched', 'key' => 'update', 'content' => 'Dont touch me'],
            ['domain' => 'd1', 'locale' => 'untouched', 'key' => 'update', 'content' => 'Dont touch me'],
        ]);

        self::assertEquals(4, $this->countRows());

        self::$driver->upsertTranslation('test', 'upsert', null, 'Upserted null domain');
        self::$driver->upsertTranslation('test', 'upsert', 'd1', 'Upserted d1 domain');
        self::$driver->upsertTranslation('test', 'brand_new', 'd1', 'Brand new d1 domain');
        self::$driver->upsertTranslation('test', 'brand_new', null, 'Brand new null domain');

        self::assertEquals(6, $this->countRows());

        self::assertEquals('Upserted null domain', self::$driver->findTranslation('test', 'upsert', null)->getContent());
        self::assertEquals('Upserted d1 domain', self::$driver->findTranslation('test', 'upsert', 'd1')->getContent());
        self::assertEquals('Brand new null domain', self::$driver->findTranslation('test', 'brand_new', null)->getContent());
        self::assertEquals('Brand new d1 domain', self::$driver->findTranslation('test', 'brand_new', 'd1')->getContent());


    }

    public function testFindTranslation()
    {
        $notFound = self::$driver->findTranslation('en', 'missing');
        self::assertNull($notFound);

        self::$driver->createTranslation('en', 'not_missing', null, 'FooBar');
        self::assertEquals(1, $this->countRows());

        $found = self::$driver->findTranslation('en', 'not_missing');
        self::assertNotNull($found);
        self::assertInstanceOf(Translation::class, $found);
        self::assertEquals('FooBar', $found->getContent());
        self::assertEquals('not_missing', $found->getKey());
        self::assertEquals('en', $found->getLocale());
        self::assertNull($found->getDomain());

        self::$driver->createTranslation('en', 'not_missing', 'cust1', 'Cust1NotMissing');
        self::assertEquals(2, $this->countRows());
        $found2 = self::$driver->findTranslation('en', 'not_missing', 'cust1');
        self::assertNotNull($found2);
        self::assertInstanceOf(Translation::class, $found2);
        self::assertEquals('Cust1NotMissing', $found2->getContent());
        self::assertEquals('not_missing', $found2->getKey());
        self::assertEquals('en', $found2->getLocale());
        self::assertEquals('cust1', $found2->getDomain());


    }

    public function testFindTranslations()
    {
        self::seedFromArray([

            ['domain' => null, 'key' => 'key1', 'locale' => 'l1', 'content' => 'content1-l1'],
            ['domain' => null, 'key' => 'key2', 'locale' => 'l1', 'content' => 'content2-l1'],
            ['domain' => null, 'key' => 'key3', 'locale' => 'l1', 'content' => 'content3-l1'],

            ['domain' => null, 'key' => 'key1', 'locale' => 'l2', 'content' => 'content1-l2'],
            ['domain' => null, 'key' => 'key2', 'locale' => 'l2', 'content' => 'content2-l2'],
            ['domain' => null, 'key' => 'key3', 'locale' => 'l2', 'content' => 'content3-l2'],

            ['domain' => 'd1', 'key' => 'key1', 'locale' => 'l1', 'content' => 'foo1-l1'],
            ['domain' => 'd1', 'key' => 'key2', 'locale' => 'l1', 'content' => 'foo2-l1'],
            ['domain' => 'd1', 'key' => 'key3', 'locale' => 'l1', 'content' => 'foo3-l1'],

            ['domain' => 'd1', 'key' => 'key1', 'locale' => 'l2', 'content' => 'foo1-l2'],
            ['domain' => 'd1', 'key' => 'key2', 'locale' => 'l2', 'content' => 'foo2-l2'],
            ['domain' => 'd1', 'key' => 'key3', 'locale' => 'l2', 'content' => 'foo3-l2'],

        ]);

        self::assertEquals(12, $this->countRows());

        // Locale l1, null domain
        $translations = self::$driver->findTranslations('l1', ['key1', 'key2', 'key3']);
        $contents = array_map(function (Translation $t) { return $t->getContent(); }, $translations);
        self::assertCount(3, $translations);
        self::assertTrue(in_array('content1-l1', $contents));
        self::assertTrue(in_array('content2-l1', $contents));
        self::assertTrue(in_array('content3-l1', $contents));

        // Locale l2, null domain
        $translations = self::$driver->findTranslations('l2', ['key1', 'key2', 'key3']);
        $contents = array_map(function (Translation $t) { return $t->getContent(); }, $translations);
        self::assertCount(3, $translations);
        self::assertTrue(in_array('content1-l2', $contents));
        self::assertTrue(in_array('content2-l2', $contents));
        self::assertTrue(in_array('content3-l2', $contents));

        // Locale l1, d1 domain
        $translations = self::$driver->findTranslations('l1', ['key1', 'key2', 'key3'], 'd1');
        $contents = array_map(function (Translation $t) { return $t->getContent(); }, $translations);
        self::assertCount(3, $translations);
        self::assertTrue(in_array('foo1-l1', $contents));
        self::assertTrue(in_array('foo2-l1', $contents));
        self::assertTrue(in_array('foo3-l1', $contents));

        // Locale l2, d2 domain
        $translations = self::$driver->findTranslations('l2', ['key1', 'key2', 'key3'], 'd1');
        $contents = array_map(function (Translation $t) { return $t->getContent(); }, $translations);
        self::assertCount(3, $translations);
        self::assertTrue(in_array('foo1-l2', $contents));
        self::assertTrue(in_array('foo2-l2', $contents));
        self::assertTrue(in_array('foo3-l2', $contents));
    }

    public function testDeleteTranslation()
    {

        self::seedFromArray([
            ['domain' => null, 'locale' => 'test', 'key' => 'preserve', 'content' => 'this is preserved'],
            ['domain' => null, 'locale' => 'test', 'key' => 'delete', 'content' => 'this is deleted'],
            ['domain' => 'd1', 'locale' => 'test', 'key' => 'preserve', 'content' => 'd1: this is preserved'],
            ['domain' => 'd1', 'locale' => 'test', 'key' => 'delete', 'content' => 'd1: this is deleted'],
        ]);

        self::assertEquals(4, $this->countRows());

        self::$driver->deleteTranslation('test', 'delete');
        self::assertEquals(3, $this->countRows());
        self::assertNull(self::$driver->findTranslation('test', 'delete', null));
        self::assertNotNull(self::$driver->findTranslation('test', 'delete', 'd1'));

        self::$driver->deleteTranslation('test', 'delete', 'd1');
        self::assertEquals(2, $this->countRows());
        self::assertNull(self::$driver->findTranslation('test', 'delete', null));
        self::assertNull(self::$driver->findTranslation('test', 'delete', 'd1'));
    }

    /**
     * @expectedException \PDOException
     */
    public function testFailsIfWrongTableName()
    {
        $driver = new PdoDriver(self::$pdo, 'badtable');
        $driver->createTranslation('en', 'hi', 'test', 'test');
    }
}
