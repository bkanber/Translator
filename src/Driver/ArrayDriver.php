<?php


namespace bkanber\Translator\Driver;

use bkanber\Translator\Exception\TranslationNotFoundException;
use bkanber\Translator\Translation;

/**
 * Class ArrayDriver
 * @package bkanber\Translator\Driver
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
