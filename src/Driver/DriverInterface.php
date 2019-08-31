<?php

namespace bkanber\Translator\Driver;

interface DriverInterface {

    public function findTranslation($locale, $key, $domain = null);

    public function findTranslations($locale, $keys, $domain = null);

    public function updateTranslation($locale, $key, $domain, $content);

    public function createTranslation($locale, $key, $domain, $content);

    public function upsertTranslation($locale, $key, $domain, $content);

    public function deleteTranslation($locale, $key, $domain = null);
}
