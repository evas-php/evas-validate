<?php
/**
 * Валидатор поля.
 * @package evas-php\evas-validate
 * @author Egor Vasyakin <egor@evas-php.com>
 */
namespace Evas\Validate;

use Evas\Base\Help\HooksTrait;
use Evas\Validate\ErrorBuilder;
use Evas\Validate\HtmlEscapingTrait;
use Evas\Validate\Interfaces\ValidatableInterface;
use Evas\Validate\ValidateException;

class Field implements ValidatableInterface
{
    // подключаем поддержку произвольных хуков в наследуемых классах
    use HooksTrait;
    // Подключаем поддержку экранирования html-тегов.
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
    /** @var mixed пришедшее значение в поле */
    public $valueBefore;
    /** @var mixed текущее значение в поле */
    public $value;
    /** @var mixed значение по умолчанию */
    public $default;
    /** @var string|array тип поля */
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

    /** @var callable колбэк для подготовки значения к валидации */
    public $prepareValueCb;


    /** @var string шаблон ошибки отсутствия значения, если поле обязательно */
    public $requiredError;
    /** @var string шаблон ошибки отсутствия функции проверки типа */
    public $undefinedTypeError;
    /** @var string шаблон ошибки неправильного типа значения */
    public $typeError;
    /** @var string шаблон ошибки длины текстового поля */
    public $lengthError;
    /** @var string шаблон ошибки непопадания в диапазон значений */
    public $rangeError;
    /** @var string шаблон ошибки непопадания в диапазон количества элементов массива/объекта */
    public $countError;
    /** @var string шаблон ошибки регулярки */
    public $patternError;
    /** @var string шаблон ошибки указания опций поля */
    public $optionsSettingError;
    /** @var string шаблон ошибки несовпадения с опциями поля */
    public $optionsError;
    /** @var string шаблон ошибки несовпадения значений в совпадающих полях */
    public $sameError;

    /** @var bool запущена ли общая валидация */
    protected $isRunCommon = false;

    /**
     * Конструктор.
     * @param array|null параметры валидатора поля
     */
    public function __construct(array $props = null)
    {
        $this->hook('beforeCreate');
        if (!empty($props)) foreach ($props as $name => $value) {
            $this->$name = $value;
        }
        $this->hook('afterCreate');
    }

    /**
     * Подготовка значения к валидации для кастомных классов валидаторов полей.
     * @param mixed значение
     * @return mixed подготовленное значение
     */
    public function prepareValue($value)
    {
        return $value;
    }

    /**
     * Общая базовая подготовка значения к валидации.
     * @param mixed значение
     * @return mixed подготовленное значение
     */
    protected function basePrepareValue($value)
    {
        // обрезание пробельных символов по краям, если включено
        if (true === $this->trim && is_string($value)) $value = trim($value);
        // подготовка значения в кастомном классе валидатора поля
        $value = $this->prepareValue($value);
        // подготовка значения колбэком этого валидатора поля
        $cb = $this->prepareValueCb;
        if (!empty($cb)) {
            $cb->bindTo($this);
            return $cb($value);
        }
        return $value;
    }

    /**
     * Установка значения валидации.
     * @param mixed|null значение
     * @return mixed|null установленное значение
     */
    protected function setValue(&$value)
    {
        if ($value !== null) $this->value = $this->basePrepareValue($value);
        $value = $this->value;
    }

    /**
     * Сборка ошибки по типу.
     * @param string тип ошибки
     * @return false
     */
    public function buildError(string $errorType): bool
    {
        $this->error = ErrorBuilder::build(
            $errorType, $this, static::MESSAGE_KEY_ALIASES
        );
        return false;
    }


    /**
     * Проверка типа значения.
     * @param mixed|null значение для проверки
     * @return bool
     */
    public function checkType($value = null): bool
    {
        if (!$this->isRunCommon) $this->setValue($value);
        if (!is_array($this->type)) $this->type = [$this->type];
        $error = 'type';
        foreach ($this->type as &$subtype) {
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
        if ($error) return $this->buildError($error);

        if (!$this->isRunCommon) $this->hook('afterValidate');
        return true;
    }

    /**
     * Проверка длины строки или диапазона значения.
     * @param mixed|null значение для проверки
     * @return bool
     */
    public function checkLength($value = null): bool
    {
        if (!$this->isRunCommon) $this->setValue($value);
        if (true === $this->checkType || !is_null($value)) {
            if (false === $this->checkType($value)) return false;
        }
        if (!is_array($this->type)) $this->type = [$this->type];
        if (null !== $this->min || null !== $this->max) {
            if (in_array('string', $this->type)) {
                $len = mb_strlen($value);
                $error = 'length';
            } else if (in_array('array', $this->type)) {
                $len = count($value ?? []);
                $error = 'count';
            } else if (in_array('object', $this->type)) {
                $len = count(get_object_vars($value ?? (object) []));
                $error = 'count';
            } else {
                $len = $value;
                $error = 'range';
            }
            if (null !== $this->min && $len < $this->min
             || null !== $this->max && $len > $this->max) {
                return $this->buildError($error);
            }
        }
        if (!$this->isRunCommon) $this->hook('afterValidate');
        return true;
    }

    /**
     * Валидация по регулярке.
     * @param mixed|null значение для проверки
     * @return bool
     */
    public function checkPattern($value = null): bool
    {
        if (!$this->isRunCommon) $this->setValue($value);
        if (!is_string($value) && !is_numeric($value) && !is_null($value)) {
            return $this->checkType($value);
        }
        if (!empty($this->pattern) && !preg_match($this->pattern, $value, $this->matches)) {
            return $this->buildError('pattern');
        }

        if (!$this->isRunCommon) $this->hook('afterValidate');
        return true;
    }

    /**
     * Проверка соответсвия опциям.
     * @param mixed|null значение для проверки
     * @return bool
     */
    public function checkOptions($value = null): bool
    {
        if (!$this->isRunCommon) $this->setValue($value);
        if (!empty($this->options)) {
            if (!is_array($this->options)) {
                return $this->buildError('optionsSetting');
            }
            if (!in_array($value, $this->options)) {
                return $this->buildError('options');
            }
        }
        if (!$this->isRunCommon) $this->hook('afterValidate');
        return true;
    }

    /**
     * Проверка значения на полную валидность полю.
     * @param mixed значение
     * @param bool|null пришло ли поле
     * @return bool
     */
    public function isValid($value, $isset = true): bool
    {
        $this->error = null;
        $this->valueBefore = $value;
        $this->isRunCommon = true;

        // если значение пустое
        if (null === $value) $value = $this->default;
        $this->setValue($value);

        if (null === $value) {
            if (true === $this->required) {
                // если обязательное поле пустое
                return $this->buildError('required');
            } else if (false === $isset) {
                // если необязательное поле не пришло
                return true;
            }
        }

        // валидация типа поля
        if (false !== $this->checkType && !$this->checkType($value)) return false;

        // экранирование html-тегов в строке
        $value = $this->escapeHtml($value, $this->name);

        // валидация длины строки или попадания числа в диапазон
        // валидация по регулярке
        // валидаци опций
        if (!$this->checkLength($value)) return false;
        if (!$this->checkPattern($value)) return false;
        if (!$this->checkOptions($value)) return false;
        
        $this->isRunCommon = false;
        $this->hook('afterValidate');

        return true;
    }

    /**
     * Проверка значения на валидность полю с выбрасом исключения.
     * @param mixed значение
     * @param bool|null пришло ли поле
     * @return self
     * @throws ValidateException
     */
    public function throwIfNotValid($value, bool $isset = true): ValidatableInterface
    {
        if (false === $this->isValid($value, $isset)) {
            throw new ValidateException($this->error);
        }
        return $this;
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
