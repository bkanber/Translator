<?php

namespace bkanber\Translator\Driver;

use bkanber\Translator\Translation;

/**
 * Interface DriverInterface
 * @package bkanber\Translator\Driver
 *
 * Implement this class in order to create a new translations lookup driver.
 * Implemented methods should generally return a Translation instance.
 *
 * This interface allows any form of constructor in order to give you freedom to deal with implementation details yourself.
 */
interface DriverInterface {

    /**
     * This method is used to look up a single translation based on locale and key, and optional domain.
     * If a domain is not provided, or set null, you should return a translation that also has a null domain.
     * That is, do not simply ignore the $domain parameter if it is null -- it should be considered a filter.
     *
     * Return null if no translation found, otherwise return a Translation instance.
     *
     * @param string $locale
     * @param string $key
     * @param string|null $domain
     * @return null|Translation
     */
    public function findTranslation($locale, $key, $domain = null);

    /**
     * This method is used to bulk-query multiple translations in a domain and a locale.
     * It should return an array in all cases. If no translations for the matching $keys are found, this should return an empty array.
     * If translations are found, it should return an array of Translation objects in no particular order.
     *
     * @param string $locale
     * @param array|string[] $keys
     * @param string|null $domain
     * @return array|Translation[]
     */
    public function findTranslations($locale, $keys, $domain = null);

    /**
     * This method updates a translation in the store. Locale, key, and domain are filters, and only the content should be updated.
     * This method should fail or return null if no translation to be updated was found.
     *
     * @param string $locale
     * @param string $key
     * @param string|null $domain
     * @param string $content
     * @return Translation
     */
    public function updateTranslation($locale, $key, $domain, $content);

    /**
     * Creates a translation in the store. This method should fail if the translation already exists. It should return the Translation object on success.
     *
     * @param string $locale
     * @param string $key
     * @param string|null $domain
     * @param string $content
     * @return Translation
     */
    public function createTranslation($locale, $key, $domain, $content);

    /**
     * This method should update an existing translation's content (if found), or create a new translation (if not found). It should therefore never fail (except for errors related to the data store itself). As with updateTranslation, $locale, $key, and $domain are filters, and only $content will be updated if the record already existed.
     *
     * @param string $locale
     * @param string $key
     * @param string|null $domain
     * @param string $content
     * @return Translation
     */
    public function upsertTranslation($locale, $key, $domain, $content);

    /**
     * Deletes a translation.
     *
     * @param string $locale
     * @param string $key
     * @param string|null $domain
     * @return bool
     */
    public function deleteTranslation($locale, $key, $domain = null);
}
