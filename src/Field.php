<?php
/**
 * Валидатор поля.
 * @package evas-php\evas-validate
 * @author Egor Vasyakin <egor@evas-php.com>
 */
namespace Evas\Validate;

use Evas\Base\Help\HooksTrait;
use Evas\Validate\HtmlEscapingTrait;
use Evas\Validate\Interfaces\ValidatableInterface;
use Evas\Validate\ValidateErrorBuilder;
use Evas\Validate\ValidateException;

class Field implements ValidatableInterface
{
    // подключаем поддержку произвольных хуков в наследуемых классах
    use HooksTrait;

    /**
     * Подключаем поддержку экранирования html-тегов.
     */
    use HtmlEscapingTrait;

    /** @static array маппинг псевдонимов подстановки */
    const MESSAGE_KEY_ALIASES = [
        'label' => 'name',
        'sameLabel' => 'same',
    ];

    /** @var string имя поля */
    public $name;
    /** @var string псевдоним поля */
    public $label;
    /** @var string пришедшее значение в поле */
    public $valueBefore;
    /** @var string текущее значение в поле */
    public $value;
    /** @var mixed значение по умолчанию */
    public $default;
    /** @var string тип поля */
    public $type = 'string';
    /** @var bool проверять ли тип значения поля */
    public $checkType = false;
    /** @var bool обязательность */
    public $required = true;
    /** @var bool делать ли очистку пробельных символов по краям строки */
    public $trim = true;

    /** @var int минимальная длина строки или значение диапазона для чисел */
    public $min;
    /** @var int максимальная длина строки или значение диапазона для чисел */
    public $max;

    /** @var string регулярное выражение */
    public $pattern;
    /** @var array совпадения по регулярке */
    public $matches;
    /** @var array опции поля */
    public $options;

    /** @var string поле с совпадающим значением */
    public $same;
    /** @var string псевдоним совпадающего поля */
    public $sameLabel;

    /** @var string сообщение ошибки */
    public $error;
    /** @var string тип поля для которого отсутствует функция проверки типа*/
    public $undefinedType;


    /** @var string сообщение об отсутствии значения, если поле обязательно */
    public $requiredError;
    /** @var string сообщение об отсутствии функции проверки типа */
    public $undefinedTypeError;
    /** @var string сообщение о неправильном типе значения */
    public $typeError;
    /** @var string сообщение об ошибке длины текстового поля */
    public $lengthError;
    /** @var string сообщение об ошибке длины, если есть только мин. длина */
    public $minLengthError;
    /** @var string сообщение об ошибке длины, если есть только макс. длина */
    public $maxLengthError;
    /** @var string сообщение об ошибке непопадания в диапазон значений */
    public $rangeError;
    /** @var string сообщение об ошибке диапазона, если есть только мин. */
    public $minRangeError;
    /** @var string сообщение об ошибке диапазона, если есть только макс. */
    public $maxRangeError;
    /** @var string сообщение об ошибке регулярки */
    public $patternError;
    /** @var string сообщение об ошибке указания опций поля */
    public $optionsSettingError;
    /** @var string сообщение об ошибке несовпадения с опциями поля */
    public $optionsError;
    /** @var string сообщение об ошибке несовпадения значений в совпадающих полях */
    public $sameError;

    /**
     * Конструктор.
     * @param array|null параметры валидатора поля
     */
    public function __construct(array $props = null)
    {
        if (!empty($props)) foreach ($props as $name => $value) {
            $this->$name = $value;
        }
        $this->hook('afterCreate');
    }

    /**
     * Подготовка значения к валидации.
     */
    public function prepareValue()
    {}

    /**
     * Сборка ошибки по типу.
     * @param string тип ошибки
     * @return false
     */
    public function buildError(string $errorType): bool
    {
        $this->error = ValidateErrorBuilder::build(
            $errorType, $this, static::MESSAGE_KEY_ALIASES
        );
        return false;
    }


    /**
     * Проверка типа значения.
     * @param mixed значение для проверки
     * @return bool
     */
    public function checkType($value): bool
    {
        $type = $this->type;
        if (is_string($type)) $type = [$type];
        $error = 'type';
        foreach ($type as &$subtype) {
            $typeCheck = 'is_' . $subtype;
            if (!is_callable($typeCheck)) {
                $error = 'undefinedType';
                $this->undefinedType = $subtype;
                break;
            } else if (call_user_func($typeCheck, $value)) {
                $error = null;
                break;
            }
        }
        return null === $error ? true : $this->buildError($error);
    }

    /**
     * Проверка длины строки или диапазона значения.
     * @param mixed значение для проверки
     * @param bool|null обрезать ли пробельные символы по краям
     * @return bool
     */
    public function checkLength($value, bool $fromValidate = false): bool
    {
        if (!is_string($value) && !is_numeric($value) && !is_null($value)) {
            return $this->checkType($value);
        }
        if (null !== $this->min || null !== $this->max) {
            if ('string' === $this->type) {
                if (true === $this->trim && !$fromValidate) $value = trim($value);
                $len = mb_strlen($value);
                $error = 'length';
            } else {
                $len = $value;    
                $error = 'range';
            }
            if (null !== $this->min && $len < $this->min
             || null !== $this->max && $len > $this->max) {
                if (null === $this->max) $error = 'min'. ucfirst($error);
                else if (null === $this->min) $error = 'max'. ucfirst($error);
                return $this->buildError($error);
            }
        }
        return true;
    }

    /**
     * Валидация по регулярке.
     * @param mixed значение для проверки
     * @param bool|null обрезать ли пробельные символы по краям
     * @return bool
     */
    public function checkPattern($value, bool $fromValidate = false): bool
    {
        if (!is_string($value) && !is_numeric($value) && !is_null($value)) {
            return $this->checkType($value);
        }
        if (true === $this->trim && !$fromValidate) $value = trim($value);
        return !empty($this->pattern)
         && !preg_match($this->pattern, $value, $this->matches)
         ? $this->buildError('pattern') : true;
    }

    /**
     * Проверка соответсвия опциям.
     * @param mixed значение для проверки
     * @param bool|null обрезать ли пробельные символы по краям
     * @return bool
     */
    public function checkOptions($value, bool $fromValidate = false): bool
    {
        if (!empty($this->options)) {
            if (!is_array($this->options)) {
                return $this->buildError('optionsSetting');
            }
            if (true === $this->trim && !$fromValidate) $value = trim($value);
            if (!in_array($value, $this->options)) {
                return $this->buildError('options');
            }
        }
        return true;
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
        $this->valueBefore = $value;
        $this->value = &$value;
        $this->prepareValue();

        // если значение пустое
        if (null === $value) {
            $value = $this->default;
            if (null === $value) {
                if (true === $this->required) {
                    // если обязательное поле пустое
                    return $this->buildError('required');
                } else if (false === $isset) {
                    // если необязательное поле не пришло
                    return true;
                }
            }
        }
        // валидация типа поля
        if (false !== $this->checkType && !$this->checkType($value)) return false;

        // экранирование html-тегов в строке
        $value = $this->escapeHtml($value, $this->name);

        // обрезание пробельных символов по краям
        if (true === $this->trim) $value = trim($value);
        // валидация длины строки или попадания числа в диапазон
        // валидация по регулярке
        // валидаци опций
        if (!$this->checkLength($value, true)) return false;
        if (!$this->checkPattern($value, true)) return false;
        if (!$this->checkOptions($value, true)) return false;

        return true;
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

    /**
     * Получение свойств валидации в формате json для фронтенда.
     * @return string json-строка свойств валидации
     */
    public function exportJson(): string
    {
        return json_encode($this);
    }
}
