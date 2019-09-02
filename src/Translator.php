<?php


namespace bkanber\Translator;

use bkanber\Translator\Driver\DriverInterface;
use bkanber\Translator\Exception\DriverMissingException;
use bkanber\Translator\Handler\HandlerInterface;
use bkanber\Translator\Parser\ParserInterface;
use bkanber\Translator\Parser\StringParser;

/**
 * Class Translator
 * @package bkanber\Translator
 *
 * This is the main translations management class.
 * In this example, the translator depends on a language and a 'database' object.
 * The database object here is a simple in-memory database. In a real implementation,
 * the database object might look different but needs to at least have a method that looks up a Translation by language and key.
 *
 * The two entrypoint methods for this class are `translateString` and `translateArray`.
 *
 * Example usage:
 *
 *   $database = TranslationsDatabase::createFromArray([['language' => 'en', 'key' => 'some_key', 'content' => 'hello world']]);
 *   $translator = new Translator('en', $database);
 *
 *   $translatedString = $translator->translateString("In english: __{{some_key, 'default text'}}");
 *   $translatedArray = $translator->translateArray(['description' => '__{{desc, "default description"}}']);
 */
class Translator
{

    /** @var array */
    protected static $instances = [];

    /** @var string */
    protected $domain;

    /** @var string */
    protected $locale;

    /** @var DriverInterface */
    protected $driver;

    /** @var HandlerInterface */
    protected $handler;

    /**
     * Singleton instance manager.
     *
     * @param string $name
     * @return static
     */
    public static function instance($name = 'default')
    {
        if (isset(self::$instances[$name])) {
            return self::$instances[$name];
        }

        self::$instances[$name] = new self();

        return self::$instances[$name];
    }

    /**
     * Translator constructor.
     * @param DriverInterface|null $driver
     */
    public function __construct(DriverInterface $driver = null)
    {
        $this->driver = $driver;
    }



    /**
     * @return HandlerInterface
     */
    public function getHandler()
    {
        return $this->handler;
    }

    /**
     * @param HandlerInterface $handler
     * @return Translator
     */
    public function setHandler($handler)
    {
        $this->handler = $handler;
        return $this;
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
     * @return static
     */
    public function setDomain($domain)
    {
        $this->domain = $domain;
        return $this;
    }

    /**
     * @return string
     */
    public function getLocale()
    {
        return $this->locale;
    }

    /**
     * @param string $locale
     * @return static
     */
    public function setLocale($locale)
    {
        $this->locale = $locale;
        return $this;
    }

    /**
     * @return DriverInterface
     */
    public function getDriver()
    {
        return $this->driver;
    }

    /**
     * @param DriverInterface $driver
     * @return static
     */
    public function setDriver($driver)
    {
        $this->driver = $driver;
        return $this;
    }


    /**
     * @param $input
     * @param ParserInterface $parser
     * @return mixed
     * @throws DriverMissingException
     */
    public function translateCustom($input, ParserInterface $parser)
    {
        if (!$this->getDriver()) {
            throw new DriverMissingException("Translator has not yet been configured with a data driver.");
        }

        $parser->setInput($input);
        $translatables = $parser->parse();

        // We get all keys at once so we can perform on DB query instead of many.
        $translationKeys = $parser->getTranslatableKeys();
        $translations = $this->getDriver()->findTranslations($this->getLocale(), $translationKeys, $this->getDomain());

        if ($this->getHandler()) {
            $this->logMissingTranslationsToHandler($translatables, $translations);
        }

        return $parser->replace($translations);
    }

    /**
     * @param $input
     * @return mixed
     * @throws DriverMissingException
     */
    public function translateString($input)
    {
        return $this->translateCustom($input, new StringParser());
    }

    /**
     * @param array $input
     * @return mixed
     * @throws DriverMissingException
     */
    public function translateArray(array $input)
    {

        foreach ($input as $key => $value) {

            if (is_string($value)) {
                $input[$key] = $this->translateString($value);
            }
            elseif (is_array($value)) {
                $input[$key] = $this->translateArray($value);
            }

        }

        return $input;

    }

    /**
     * @param array|Translatable[] $translatables
     * @param array|Translation[] $translations
     */
    protected function logMissingTranslationsToHandler(array $translatables, array $translations)
    {
        if (!$this->getHandler()) {
            return;
        }

        $translationMap = [];

        foreach ($translations as $translation) {
            $translationMap[$translation->getKey()] = $translation;
        }

        foreach ($translatables as $translatable) {
            if (!isset($translationMap[$translatable->getKey()])) {
                $this->getHandler()->onMissingTranslation($translatable, $this);
            }
        }
    }
}
