<?php
/**
 * @package evas-php/evas-validate
 */
namespace Evas\Validate;

use Evas\Validate\Fieldset;
use Evas\Validate\ValidateException;

/**
 * Валидатор набора полей json.
 * @author Egor Vasyakin <e.vasyakin@itevas.ru>
 * @since 1.0
 */
class JsonFieldset extends Fieldset
{
    /**
     * @static string ошибка
     */
    const ERROR_JSON_PARSE = 'Ошибка разбора json';

    /**
     * @var @static string ошибка разбора json
     */
    public static $jsonParseError = self::ERROR_JSON_PARSE;

    /**
     * Переопределяем валидацию данных для разбора json.
     * @param string json строка значений [поле => значение]
     * @param bool|false проверять на все ошибки
     * @return bool
     */
    public function isValid($values, $multipleErrors = false): bool
    {
        $values = json_decode($values, true);
        if (!is_array($values)) {
            throw new ValidateException(static::$jsonParseError);
        }
        return parent::isValid($values, $multipleErrors);
    }
}
