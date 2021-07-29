<?php
/**
 * Сборщик шаблонизированных ошибок валидации.
 * @package evas-php\evas-validate
 * @author Egor Vasyakin <egor@evas-php.com>
 */
namespace Evas\Validate;

use Evas\Base\App;
use Evas\Base\Help\StringTemplator;

if (!defined('EVAS_VALIDATE_ERRORS_FILE')) {
    define('EVAS_VALIDATE_ERRORS_FILE', __DIR__.'/config/errors_default.php');
}

class ValidateErrorBuilder
{
    /** @static string путь к файлу шаблонов ошибок по кодам */
    const ERRORS_FILE = EVAS_VALIDATE_ERRORS_FILE;
    /** @static string путь к файлу кодов ошибок по типам */
    const ERROR_CODES_FILE = __DIR__.'/config/error_codes.php';

    /** @static array шаблоны ошибок по кодам */
    protected static $templateByCodes = [];

    /** @static array коды ошибок по типам */
    protected static $codeByTypes = [];

    /** @static StringTemplator шаблонизатор ошибок */
    public static $templator;

    /**
     * Получение маппинга шаблонов ошибок по кодам.
     * @return array
     */
    public static function templateByCodes(): array
    {
        if (!static::$templateByCodes) {
            static::$templateByCodes = include static::ERRORS_FILE;
        }
        return static::$templateByCodes;
    }

    /**
     * Получение маппинга кодов ошибок по типам.
     * @return array
     */
    public static function codeByTypes(): array
    {
        if (!static::$codeByTypes) {
            static::$codeByTypes = include_once static::ERROR_CODES_FILE;
        }
        return static::$codeByTypes;
    }

    /**
     * Обновление шаблонов ошибок по кодам из файла.
     * @param string имя файла
     */
    public static function includeErrorsByCodes(string $filename)
    {
        $filename = App::resolveByApp($filename);
        $updates = include $filename;
        if (!is_array($updates)) {
            throw new \Exception(sprintf(
                'Validate errors map must be array, %s given',
                gettype($updates)
            ));
        }
        static::updateErrorsByCodes($updates);
    }

    /**
     * Обновление шаблонов ошибок по кодам.
     * @param array маппинг шаблонов ошибок по кодам
     */
    public static function updateErrorsByCodes(array $templateByCodes)
    {
        foreach ($templateByCodes as $code => &$error) {
            static::updateErrorByCode($code, $error);
        }
    }

    /**
     * Обновление шаблона ошибки по коду.
     * @param int код ошибки
     * @param string шаблон ошибки
     */
    public static function updateErrorByCode(int $code, string $error)
    {
        static::templateByCodes();
        static::$templateByCodes[$code] = $error;
    }

    /**
     * Обновление шаблонов ошибок по типам.
     * @param array маппинг шаблонов ошибок по типам
     */
    public static function updateErrorsByTypes(array $errorByTypes)
    {
        foreach ($errorByTypes as $type => &$error) {
            $code = static::codeByTypes()[$type] ?? null;
            static::updateErrorByCode($code, $error);
        }
    }

    /**
     * Получение шаблона ошибки по типу.
     * @param string тип ошибки
     * @return string шаблон ошибки
     */
    public static function templateByType(string $type): string
    {
        return static::templateByCodes()[static::codeByTypes()[$type] ?? 0];
    }

    /**
     * Получение шаблона ошибки по коду.
     * @param int код ошибки
     * @return string шаблон ошибки
     */
    public static function templateByCode(int $code): string
    {
        return static::templateByCodes()[$code] ?? static::templateByCodes()[0];
    }

    /**
     * Получение сборщика ошибки.
     * @return StringTemplator
     */
    public static function templator()
    {
        if (!static::$templator) {
            static::$templator = new StringTemplator([
                'varOpenSym' => ':',
                'varCloseSym' => '',
                'optiOpenSym' => '<',
                'optiCloseSym' => '>',
            ]);
        }
        return static::$templator;
    }

    /**
     * Сборка сообщения ошибки.
     * @param string тип ошибки
     * @param object контекст ошибки
     * @param array|null алиасы для свойств подстановки
     * @return string сообщение
     */
    public static function build(
        string $type, object &$context, 
        array $keyAliases = null
    ): string {
        $var = "{$type}Error";
        $template = $context->$var ?? null;
        return static::templator()->build(
            $template ?? static::templateByType($type), $context, $keyAliases
        );
    }


    // public $code;
    // public $type;
    // public $message;

    // public function __construct(int $code, string $message)
    // {
    //     $this->code = $code;
    //     $this->type = static::TYPE_BY_CODES[$code] ?? static::TYPE_BY_CODES[0];
    //     $this->message = $message;
    // }

    // public static function getCode(): int
    // {
    //     return $this->code;
    // }

    // public static function getType(): string
    // {
    //     return $this->type;
    // }

    // public static function getMessage(): string
    // {
    //     return $this->message;
    // }

    // public function __toString()
    // {
    //     return $this->getMessage();
    // }
}
