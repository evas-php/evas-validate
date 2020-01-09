<?php
/**
 * @package evas-php/evas-validate
 */
namespace Evas\Validate;

use Evas\Base\MessageVarsReplacerTrait;
use Evas\Validate\HtmlQuoteTrait;
use Evas\Validate\ValidateException;

/**
 * Валидатор поля.
 * @author Egor Vasyakin <e.vasyakin@itevas.ru>
 * @since 1.0
 */
class Field
{
    /**
     * Подключем трейт подстановки переменных в сообщение.
     */
    use MessageVarsReplacerTrait;

    /**
     * Подключаем поддержку экранирования html-тегов.
     */
    use HtmlQuoteTrait;

    /**
     * @static string сообщение ошибки по умолчанию
     */
    const ERROR_REQUIRED = 'Отсутствует обязательное поле ":label"';
    const ERROR_TYPE_UNDEFINED = 'Не найден метод проверки типа :type для поля ":label"';
    const ERROR_TYPE = 'Значение поля ":label" должно быть типа :type';
    const ERROR_LENGTH = 'Значение поля ":label" должно быть длиной< от :min>< до :max> символов';
    const ERROR_RANGE = 'Значение поля ":label" должно быть< от :min>< до :max>';
    const ERROR_PATTERN = 'Проверьте правильность поля ":label"';
    const ERROR_SAME = 'Значения полей ":label" и ":sameLabel" должны совпадать';
    const ERROR_OPTIONS = 'Значение поля ":label" не соответствует заданным опциям';
    const ERROR_OPTIONS_SETTING = 'Неверно указаны опции поля ":label"';

    /**
     * @static array маппинг псевдонимов подстановки
     */
    const ERROR_KEYS_ALIASES = [
        'label' => 'name',
        'sameLabel' => 'same',
    ];

    /**
     * @var string имя поля
     */
    public $name;

    /**
     * @var string псевдоним поля
     */
    public $label;

    /**
     * @var string пришедшее текущее значение в поле
     */
    public $value;

    /**
     * @var string тип поля
     */
    public $type = 'string';

    /**
     * @var bool проверять ли тип значения поля
     */
    public $typeChecked = false;

    /**
     * @var bool обязательность
     */
    public $required = true;

    /**
     * @var bool делать ли очистку пробельных символов по краям строки
     */
    public $trim = true;

    /**
     * @var int минимальная длина строки или значение диапазона для чисел
     */
    public $min;

    /**
     * @var int максимальная длина строки или значение диапазона для чисел
     */
    public $max;

    /**
     * @var string регулярное выражение
     */
    public $pattern;

    /**
     * @var array совпадения по регулярке
     */
    public $matches;

    /**
     * @var string поле с совпадающим значением
     */
    public $same;

    /**
     * @var string псевдоним совпадающего поля
     */
    public $sameLabel;

    /**
     * @var mixed значение по умолчанию
     */
    public $default;

    /**
     * @var array опции поля
     */
    public $options;

    /**
     * @var string сообщение ошибки
     */
    public $error;


    /**
     * Если вы хотите предустановить свои сообщения по умолчанию,
     * унаследуйте этот класс и переопределите в нём эти переменные,
     * а также, если вы используете валидацию набора полей, установите
     * в нём свой кастомный валидатор поля для валидации полей.
     */

    /**
     * @var string сообщение об отсутствии, если обязательно
     */
    public $requiredError = self::ERROR_REQUIRED;

    /**
     * @var string сообщение об отсутствии, если обязательно
     */
    public $typeUndefinedError = self::ERROR_TYPE_UNDEFINED;

    /**
     * @var string сообщение об отсутствии, если обязательно
     */
    public $typeError = self::ERROR_TYPE;

    /**
     * @var string сообщение об ошибке длины текстового поля
     */
    public $lengthError = self::ERROR_LENGTH;

    /**
     * @var string сообщение об ошибке непопадания в диапазон значений
     */
    public $rangeError = self::ERROR_RANGE;

    /**
     * @var string сообщение об ошибке регулярки
     */
    public $patternError = self::ERROR_PATTERN;

    /**
     * @var string сообщение об ошибке несовпадения значений в совпадающих полях
     */
    public $sameError = self::ERROR_SAME;

    /**
     * @var string сообщение об ошибке несовпадения с опциями поля
     */
    public $optionsError = self::ERROR_OPTIONS;

    /**
     * @var string сообщение об ошибке указания опций поля
     */
    public $optionsSettingError = self::ERROR_OPTIONS_SETTING;

    /**
     * Конструктор.
     * @param array|object параметры валидатора
     */
    public function __construct($params = null)
    {
        if ($params) {
            assert(is_array($params) || is_object($params));
            foreach ($params as $name => $value) {
                $this->$name = $value;
            }
        }
    }

    /**
     * Подготовка значения к валидации.
     * @param mixed значение
     * @return mixed подготовленное значение
     */
    public function prepareValue($value)
    {
        return $value;
    }

    /**
     * Проверка значения на валидность полю.
     * @param mixed значение
     * @param bool пришло ли поле
     * @return bool
     */
    public function isValid($value, $isset = true): bool
    {
        $this->error = null;
        $this->value = $this->prepareValue($value);
        
        // если значение пустое
        if (null === $value) {
            $this->value = $value = $this->default;
            if (null === $value) {
                // если обязательное поле пустое
                if ($this->required == true) {
                    return $this->setError(
                        $this->requiredError ?? $this->buildError(static::ERROR_REQUIRED)
                    );
                } else if ($isset === false) {
                // если необязательное поле не пришло
                    return true;
                }
            }
        }

        // валидация типа поля
        if (false !== $this->typeChecked) {
            if (! $this->checkType($value)) return false;
        }

        // очистка пробельных символов по краям строки
        if (is_string($value) && true === $this->trim) {
            $this->value = $value = trim($value);
        }

        // экранирование html-тегов в строке
        $this->value = $value = $this->quoteHtmlValue($value, $this->name);

        // валидация длины строки или попадания числа в диапазон
        if (! $this->checkLength($value)) return false;

        // валидация по регулярке
        if (! $this->checkPattern($value)) return false;

        // валидаци опций
        if (! $this->checkOptions($value)) return false;

        return true;
    }

    /**
     * Проверка типа значения.
     * @param mixed значение для проверки
     * @return bool
     */
    public function checkType($value): bool
    {
        $type = $this->type;
        if (is_string($type)) {
            $type = [$this->type];
        }
        $error = $this->typeError;
        foreach ($type as &$subtype) {
            $typeCheck = 'is_' . $subtype;
            if (!is_callable($typeCheck)) {
                $error = $this->typeUndefinedError;
            }
            if (call_user_func($typeCheck, $value)) {
                $error = null;
            }
        }
        if ($error) {
            return $this->setError($error);
        }
        return true;
    }

    /**
     * Проверка длины строки или диапазона значения.
     * @param mixed значение для проверки
     * @return bool
     */
    public function checkLength($value): bool
    {
        if (!is_string($value) && !is_numeric($value) && !is_null($value)) {
            return $this->checkType($value);
        }
        if (null !== $this->min || null !== $this->max) {
            if ('string' === $this->type) {
                $len = mb_strlen($value);
                $error = $this->lengthError;
            } else {
                $len = $value;    
                $error = $this->rangeError;
            }
            if (null !== $this->min && $len < $this->min || null !== $this->max && $len > $this->max) {
                return $this->setError($error);
            }
        }
        return true;
    }

    /**
     * Валидация по регулярке.
     * @param mixed значение для проверки
     * @return bool
     */
    public function checkPattern($value): bool
    {
        if (!is_string($value) && !is_numeric($value) && !is_null($value)) {
            return $this->checkType($value);
        }
        if (!empty($this->pattern) && !preg_match($this->pattern, $value, $this->matches)) {
            return $this->setError($this->patternError);
        }
        return true;
    }

    /**
     * Проверка соответсвия опциям.
     * @param mixed значение для проверки
     * @return bool
     */
    public function checkOptions($value): bool
    {
        if (!empty($this->options)) {
            if (!is_array($this->options)) {
                return $this->setError($this->optionsSettingError);
            }
            if (!in_array($value, $this->options)) {
                return $this->setError($this->optionsError);
            }
        }
        return true;
    }


    /**
     * Установка ошибки.
     * @param string сообщение ошибки
     * @return false
     */
    public function setError(string $message = null)
    {
        $this->error = $this->setMessageVars($message);
        return false;
    }

    /**
     * Проверка значения на валидность полю с выбрасом исключения.
     * @param mixed значение
     * @param bool пришло ли поле
     * @throws ValidateException
     */
    public function throwIfNotValid($value, bool $isset = true)
    {
        if (false === $this->isValid($value, $isset)) {
            throw new ValidateException($this->error);
        }
    }
} 
