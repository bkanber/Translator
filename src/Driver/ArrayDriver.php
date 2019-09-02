<?php


namespace bkanber\Translator\Driver;

use bkanber\Translator\Exception\TranslationNotFoundException;
use bkanber\Translator\Translation;

/**
 * Class ArrayDriver
 * @package bkanber\Translator\Driver
 *
 * This class allows you to load a translations catalog in-memory, without using a database datastore.
 * There are several ways to instantiate this driver, depending on your needs.
 *
 * Adding translations one at a time:
 *
 * ```php
 * $driver = new ArrayDriver();
 *
 * $driver->createTranslation($locale, $key, $domain, $content);
 * $driver->createTranslation($locale, $key, $domain, $content);
 * // etc...
 *
 * $translator = new Translator($driver);
 * ```
 *
 * You can add translations after the Translator has been instantiated:
 *
 * ```php
 * $translator = new Translator(new ArrayDriver());
 *
 * $translator->getDriver()->createTranslation( ... );
 * ```
 *
 * You can generate Translation instances and use setTranslations:
 *
 * ```
 * $driver = new ArrayDriver();
 *
 * $translations = [
 *     new Translation($locale, $key, $content, $domain),
 *     new Translation($locale, $key, $content, $domain),
 *     new Translation($locale, $key, $content, $domain)
 * ];
 *
 * $driver->setTranslations($translations);
 * ```
 *
 * Or you can use the static createFromArray method:
 *
 * ```php
 *
 * $translator = new Translator(ArrayDriver::createFromArray([
 *     ['locale' => 'en', 'key' => 'foo', 'content' => 'bar', 'domain' => 'cust1'],
 *     ['locale' => 'en', 'key' => 'foo', 'content' => 'bar'],
 *     ['locale' => 'en', 'key' => 'foo', 'content' => 'bar'],
 *     // ... etc
 * ]));
 *
 * ```
 *
 * Locale, key, and content are all required for Translations. Only domain is optional.
 */
class ArrayDriver implements DriverInterface
{

    /** @var array|Translation[] */
    protected $translations = [];

    /**
     * @param $translations
     * @return ArrayDriver
     */
    public static function createFromArray($translations)
    {
        return (new static())
            ->setTranslations(
                array_map(function ($trans) {
                    return Translation::createFromArray($trans);
                }, $translations)
            );
    }

    /**
     * @return array|Translation[]
     */
    public function getTranslations()
    {
        return $this->translations;
    }

    /**
     * @param array|Translation[] $translations
     * @return ArrayDriver
     */
    public function setTranslations($translations)
    {
        $this->translations = $translations;
        return $this;
    }


    /**
     * @param string $locale
     * @param string $key
     * @param string|null $domain
     * @return Translation|mixed|null
     */
    public function findTranslation($locale, $key, $domain = null)
    {
        foreach ($this->getTranslations() as $translation) {
            if (
                $translation->getLocale() === $locale
                && $translation->getDomain() === $domain
                && $translation->getKey() === $key
            ) {
                return $translation;
            }
        }

        // Not found.
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
        return array_values(
            array_filter(
                $this->getTranslations(),
                function (Translation $translation) use ($locale, $keys, $domain) {
                    return (
                        $translation->getDomain() === $domain
                        && $translation->getLocale() === $locale
                        && in_array($translation->getKey(), $keys, true)
                    );
                }
            )
        );
    }

    /**
     * @param string $locale
     * @param string $key
     * @param string|null $domain
     * @return bool
     */
    public function deleteTranslation($locale, $key, $domain = null)
    {
        $translations = $this->getTranslations();
        $deleted = false;

        foreach ($translations as $index => $translation) {
            if (
                $translation->getKey() === $key
                && $translation->getLocale() === $locale
                && $translation->getDomain() === $domain
            ) {
                unset($translations[$index]);
                $deleted = true;
            }
        }

        $this->setTranslations(array_values($translations));

        return $deleted;
    }

    /**
     * @param string $locale
     * @param string $key
     * @param string $domain
     * @param string $content
     * @return Translation
     * @throws TranslationNotFoundException
     */
    public function updateTranslation($locale, $key, $domain, $content)
    {
        $translation = $this->findTranslation($locale, $key, $domain);

        if (!$translation) {
            throw new TranslationNotFoundException("Translation not found. Try upsertTranslation instead.");
        }

        $translation->setContent($content);

        return $translation;
    }

    /**
     * @param string $locale
     * @param string $key
     * @param string $domain
     * @param string $content
     * @return Translation
     */
    public function createTranslation($locale, $key, $domain, $content)
    {
        $translation = new Translation($locale, $key, $content, $domain);
        $this->translations[] = $translation;
        return $translation;
    }

    /**
     * @param $locale
     * @param $key
     * @param $domain
     * @param $content
     * @return Translation|mixed|null
     */
    public function upsertTranslation($locale, $key, $domain, $content)
    {
        $translation = $this->findTranslation($locale, $key, $domain);

        if ($translation) {
            return $this->updateTranslation($locale, $key, $domain, $content);
        } else {
            return $this->createTranslation($locale, $key, $domain, $content);
        }
    }
}
