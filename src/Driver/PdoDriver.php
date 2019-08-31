<?php


namespace bkanber\Translator\Driver;


use bkanber\Translator\Exception\PdoDriverException;
use bkanber\Translator\Translation;

/**
 * Class PdoDriver
 * @package bkanber\Translator\Driver
 */
class PdoDriver implements DriverInterface
{

    /** @var \PDO */
    protected $pdo;

    /**
     * PdoDriver constructor.
     * @param \PDO $pdo
     */
    public function __construct(\PDO $pdo)
    {
        $this->pdo = $pdo;
    }

    /**
     * @return \PDO
     */
    public function getPdo()
    {
        return $this->pdo;
    }

    /**
     * @param \PDO $pdo
     * @return PdoDriver
     */
    public function setPdo($pdo)
    {
        $this->pdo = $pdo;
        return $this;
    }

    /**
     * @param string $locale
     * @param string $key
     * @param string|null $domain
     * @return Translation|null
     * @throws \bkanber\Translator\Exception\MalformedTranslationException
     */
    public function findTranslation($locale, $key, $domain = null)
    {
        $bindings = [':locale' => $locale, ':key' => $key];
        $sql = 'SELECT * FROM translations WHERE locale = :locale AND key = :key ';

        if ($domain) {
            $sql .= ' AND domain = :domain ';
            $bindings[':domain'] = $domain;
        } else {
            $sql .= 'AND domain IS NULL ';
        }

        $sql .= ' LIMIT 1;';

        $stmt = $this->pdo->prepare($sql);
        $stmt->execute($bindings);

        $rows = $stmt->fetchAll(\PDO::FETCH_ASSOC);

        if (isset($rows[0])) {
            return Translation::createFromArray($rows[0]);
        }

        return null;
    }

    /**
     * @param string $locale
     * @param array|string[] $keys
     * @param string|null $domain
     * @return array|Translation[]
     */
    public function findTranslations($locale, $keys, $domain = null)
    {
        $bindings = [
            $locale
        ];

        $sql = 'SELECT * FROM translations WHERE locale = ? ';

        if ($domain) {
            $sql .= ' AND domain = ? ';
            $bindings[] = $domain;
        } else {
            $sql .= ' AND domain IS NULL ';
        }

        $sql .= ' AND key IN (' . implode(',', array_fill(0, count($keys), '?')) . ');';
        $bindings = array_merge($bindings, $keys);

        $stmt = $this->pdo->prepare($sql);
        $stmt->execute($bindings);
        $rows = $stmt->fetchAll(\PDO::FETCH_ASSOC);

        return array_map(function ($row) {
            return Translation::createFromArray($row);
        }, $rows);
    }

    /**
     * @param $locale
     * @param $key
     * @param $domain
     * @param $content
     * @return Translation|null
     * @throws \bkanber\Translator\Exception\MalformedTranslationException
     */
    public function updateTranslation($locale, $key, $domain, $content)
    {
        $bindings = [
            ':locale' => $locale,
            ':key' => $key,
            ':content' => $content
        ];

        $sql = 'UPDATE translations SET content = :content WHERE locale = :locale AND key = :key ';

        if ($domain) {
            $sql .= ' AND domain = :domain;';
            $bindings[':domain'] = $domain;
        } else {
            $sql .= ' AND domain IS NULL;';
        }

        $stmt = $this->pdo->prepare($sql);
        $stmt->execute($bindings);

        return $this->findTranslation($locale, $key, $domain);
    }

    /**
     * @param $locale
     * @param $key
     * @param $domain
     * @param $content
     * @return Translation
     * @throws PdoDriverException
     * @throws \bkanber\Translator\Exception\MalformedTranslationException
     */
    public function createTranslation($locale, $key, $domain, $content)
    {
        $bindings = [
            'locale' => $locale,
            'key' => $key,
            'domain' => $domain,
            'content' => $content
        ];

        $sql = 'INSERT INTO translations (locale, key, domain, content) VALUES (:locale, :key, :domain, :content);';
        $stmt = $this->pdo->prepare($sql);
        $success = $stmt->execute($bindings);

        if (!$success) {
            throw new PdoDriverException("Could not create translation");
        }

        return Translation::createFromArray($bindings);
    }

    /**
     * @param string $locale
     * @param string $key
     * @param string|null $domain
     * @param string $content
     * @return Translation
     * @throws PdoDriverException
     * @throws \bkanber\Translator\Exception\MalformedTranslationException
     */
    public function upsertTranslation($locale, $key, $domain, $content)
    {
        $exists = $this->findTranslation($locale, $key, $domain);

        if ($exists) {
            return $this->updateTranslation($locale, $key, $domain, $content);
        } else {
            return $this->createTranslation($locale, $key, $domain, $content);
        }
    }

    /**
     * @param string $locale
     * @param string $key
     * @param string|null $domain
     * @return bool
     */
    public function deleteTranslation($locale, $key, $domain = null)
    {
        $bindings = [
            ':locale' => $locale,
            ':key' => $key
        ];

        $sql = 'DELETE FROM translations WHERE locale = :locale AND key = :key ';
        if ($domain) {
            $sql .= ' AND domain = :domain ;';
            $bindings[':domain'] = $domain;
        } else {
            $sql .= ' AND domain IS NULL ;';
        }

        $stmt = $this->pdo->prepare($sql);
        return $stmt->execute($bindings);
    }
}
