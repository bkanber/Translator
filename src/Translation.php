<?php


namespace bkanber\Translator;


use bkanber\Translator\Exception\MalformedTranslationException;

/**
 * Class Translation
 * @package bkanber\Translator
 *
 * This class represents a database model, a representation of a single key/value translation in a single language.
 * Normally this data would be stored in the DB.
 */
class Translation
{

    /** @var string */
    protected $domain;

    /** @var string */
    protected $locale;

    /** @var string */
    protected $key;

    /** @var string */
    protected $content;

    /**
     * @param array $translation Requires locale, key, and content array keys. 'domain' array key is optional.
     *
     * @return Translation
     * @throws MalformedTranslationException
     */
    public static function createFromArray($translation)
    {
        if (!isset($translation['locale'])) {
            throw new MalformedTranslationException("Translation requires a locale");
        }

        if (!isset($translation['key'])) {
            throw new MalformedTranslationException("Translation requires a key");
        }

        if (!isset($translation['content'])) {
            throw new MalformedTranslationException("Translation requires content");
        }

        return new static(
            $translation['locale'],
            $translation['key'],
            $translation['content'],
            isset($translation['domain']) ? $translation['domain'] : null
        );

    }

    /**
     * Translation constructor.
     * @param string $locale
     * @param string $key
     * @param string $content
     * @param string|null $domain
     */
    public function __construct($locale, $key, $content, $domain = null)
    {
        $this->locale = $locale;
        $this->key = $key;
        $this->content = $content;
        $this->domain = $domain;
    }

    /**
     * @return mixed
     */
    public function getLocale()
    {
        return $this->locale;
    }

    /**
     * @param mixed $locale
     */
    public function setLocale($locale)
    {
        $this->locale = $locale;
    }

    /**
     * @return mixed
     */
    public function getKey()
    {
        return $this->key;
    }

    /**
     * @param mixed $key
     */
    public function setKey($key)
    {
        $this->key = $key;
    }

    /**
     * @return mixed
     */
    public function getContent()
    {
        return $this->content;
    }

    /**
     * @param mixed $content
     */
    public function setContent($content)
    {
        $this->content = $content;
    }


    /**
     * @return string
     */
    public function getDomain()
    {
        return $this->domain;
    }

    /**
     * @param string $domain
     */
    public function setDomain($domain)
    {
        $this->domain = $domain;
    }


}
