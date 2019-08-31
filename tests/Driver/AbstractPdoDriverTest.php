<?php


namespace bkanber\Translator\Tests\Driver;


use bkanber\Translator\Driver\PdoDriver;
use Symfony\Component\Console\Input\StringInput;
use Symfony\Component\Console\Output\NullOutput;

/**
 * Class AbstractPdoDriverTest
 * @package bkanber\Translator\Tests\Driver
 *
 * Extend this test class to set up an in-memory sqlite DB with a PdoDriver
 */
abstract class AbstractPdoDriverTest extends \PHPUnit_Framework_TestCase
{
    /** @var \PDO */
    protected static $pdo;

    /** @var PdoDriver */
    protected static $driver;


    /**
     * Creates an in-memory sqlite DB and pdo handle, runs Phinx migrations, and inits a PdoDriver
     */
    public static function setUpBeforeClass()
    {
        self::$pdo = new \PDO('sqlite::memory:', null, null, [
            \PDO::ATTR_ERRMODE => \PDO::ERRMODE_EXCEPTION,
            \PDO::ATTR_DEFAULT_FETCH_MODE => \PDO::FETCH_ASSOC
        ]);

        $dbConfig = [
            'paths' => [
                'migrations' => realpath(__DIR__ . '/../../dist/migrations')
            ],
            'environments' => [
                'test' => [
                    'adapter' => 'sqlite',
                    'connection' => self::$pdo
                ]
            ]
        ];

        $config = new \Phinx\Config\Config($dbConfig);
        $manager = new \Phinx\Migration\Manager($config, new StringInput(' '), new NullOutput());
        $manager->migrate('test');

        self::$driver = new PdoDriver(self::$pdo);
    }

    /**
     * @return int
     */
    protected function countRows()
    {
        return (int) self::$pdo->query('select count(*) as count from translations;')->fetchAll()[0]['count'];
    }

    /**
     * @return bool
     */
    protected function truncate()
    {
        return self::$pdo
            ->query("delete from translations; delete from sqlite_sequence where name = 'translations';")
            ->execute();
    }

    /**
     * @param $array
     * @throws \bkanber\Translator\Exception\MalformedTranslationException
     * @throws \bkanber\Translator\Exception\PdoDriverException
     */
    protected static function seedFromArray($array)
    {
        foreach ($array as $row) {
            self::$driver->createTranslation(
                $row['locale'],
                $row['key'],
                isset($row['domain']) ? $row['domain'] : null,
                $row['content']
            );
        }
    }

    public function testDriverIsHealthy()
    {
        $stmt = self::$driver->getPdo()->query('select 1 as test;');
        $stmt->execute();
        $rows = $stmt->fetchAll();
        self::assertCount(1, $rows);
        self::assertEquals(1, $rows[0]['test']);
    }
}
