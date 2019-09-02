<?php


namespace bkanber\Translator\Handler;


use bkanber\Translator\Exception\BadResourceException;
use bkanber\Translator\Translatable;
use bkanber\Translator\Translator;

/**
 * Class LogFileHandler
 * @package bkanber\Translator\Handler
 */
class LogFileHandler implements HandlerInterface
{

    /** @var resource */
    private $file;

    /**
     * LogFileHandler constructor.
     * @param resource $file
     * @throws BadResourceException
     */
    public function __construct($file)
    {
        if (!is_resource($file)) {
            throw new BadResourceException("LogFileHandler must be instantiated with a file handle resource");
        }

        $this->file = $file;
    }

    public function __destruct()
    {
        if (is_resource($this->file)) {
            fclose($this->file);
        }
    }

    /**
     * @param Translatable $translatable
     * @param Translator $translator
     * @throws \Exception
     */
    public function onMissingTranslation(Translatable $translatable, Translator $translator)
    {
        $time = new \DateTime();

        $line = implode("\t", [
            $time->format(\DateTime::ISO8601),
            $translator->getDomain(),
            $translator->getLocale(),
            $translatable->getKey(),
            $translatable->getDefault(),
            $translatable->getReplace()
        ]) . "\n";

        fwrite($this->file, $line);
    }
}
