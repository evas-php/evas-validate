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
    define('EVAS_VALIDATE_ERRORS_FILE', __DIR__.'/../config/errors_default.php');
}

class ErrorBuilder
{
    /** @static string путь к файлу шаблонов ошибок по кодам */
    const ERRORS_FILE = EVAS_VALIDATE_ERRORS_FILE;
    /** @static string путь к файлу кодов ошибок по типам */
    const ERROR_CODES_FILE = __DIR__.'/../config/error_codes.php';

    /** @static array шаблоны ошибок по кодам */
    protected static $templates = [];

    /** @static array коды ошибок по типам */
    protected static $codes = [];

    /** @static StringTemplator шаблонизатор ошибок */
    public static $templator;

    /**
     * Получение маппинга шаблонов ошибок по кодам.
     * @return array
     */
    public static function templates(): array
    {
        if (!static::$templates) {
            static::$templates = include static::ERRORS_FILE;
        }
        return static::$templates;
    }

    /**
     * Получение маппинга кодов ошибок по типам.
     * @return array
     */
    public static function codes(): array
    {
        if (!static::$codes) {
            static::$codes = include_once static::ERROR_CODES_FILE;
        }
        return static::$codes;
    }

    /**
     * Обновление шаблонов ошибок из файла.
     * @param string имя файла
     */
    public static function includeTemplates(string $filename)
    {
        $filename = App::resolveByApp($filename);
        $updates = include $filename;
        if (!is_array($updates)) {
            throw new \Exception(sprintf(
                'Validate errors map must be array, %s given',
                gettype($updates)
            ));
        }
        static::templates();
        foreach ($updates as $code => &$error) {
            static::$templates[$code] = $error;
        }
    }

    /**
     * Получение шаблона ошибки по типу.
     * @param string тип ошибки
     * @return string шаблон ошибки
     */
    public static function templateByType(string $type): string
    {
        return static::templates()[static::codes()[$type] ?? 0];
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
}
