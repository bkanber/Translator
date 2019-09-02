<?php


namespace bkanber\Translator\Handler;


use bkanber\Translator\Translatable;
use bkanber\Translator\Translator;

interface HandlerInterface
{

    public function onMissingTranslation(Translatable $translatable, Translator $translator);

}
