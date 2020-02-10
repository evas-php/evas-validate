<?php
/**
 * @package evas-php/evas-validate
 */
namespace Evas\Validate;

use Evas\Validate\Field;
use Evas\Validate\Errors;
use Evas\Validate\HtmlQuoteTrait;
use Evas\Validate\ValidateException;

/**
 * Валидатор набора полей.
 * @author Egor Vasyakin <egor@evas-php.com>
 * @since 1.0
 */
class Fieldset
{
    /**
     * Подключаем поддержкуэкранирования html-тегов.
     */
    use HtmlQuoteTrait;

    /**
     * @var string имя набора полей, если это вложенный набор полей
     */
    public $name;

    /**
     * @var array валидаторы полей [имя поля => валидатор]
     */
    public $fields = [];

    /**
     * @var Errors объект ошибок
     */
    protected $errors;

    /**
     * @var array значения после валидации [имя поля => значение]
     */
    public $values = [];

    /**
     * @var array исходные значения до валидации [имя поля => значение]
     */
    public $valuesBefore = [];

    /**
     * @var string класс валидатора поля
     */
    public $fieldClass = Field::class;

    /**
     * Конструктор.
     * @param array [имя поля => валидатор] или [имя поля => параметры валидатора]
     */
    public function __construct(array $fields = null)
    {
        if ($fields) $this->fields($fields);
        $this->errors = new Errors;
    }

    /**
     * Установка класса валидатора поля.
     * @param string
     * @return self
     */
    public function fieldClass(string $fieldClass)
    {
        $this->fieldClass = $fieldClass;
        return $this;
    }

    /**
     * Установка валидаторов полей.
     * @param array [имя поля => валидатор] или [имя поля => параметры валидатора]
     * @return self
     */
    public function fields(array $fields)
    {
        foreach ($fields as $name => $field) {
            $this->field($name, $field);
        }
        return $this;
    }

    /**
     * Установка валидатора поля.
     * @param string имя поля
     * @param array|Field валидатор или параметры валидатора
     * @return self
     */
    public function field(string $name, $field)
    {
        assert(is_array($field) || $field instanceof Field || $field instanceof self || $field instanceof $this->fieldClass);
        if (is_array($field)) {
            $field = new $this->fieldClass($field);
        }
        $field->name = $name;
        $this->fields[$name] = $field;
        return $this;
    }

    /**
     * Установка ошибки/ошибок.
     * @param string|array ключ поля или маппинг ошибок по полям
     * @param string|null ошибка, если не маппинг
     */
    public function setError($key, string $value = null)
    {
        assert(is_string($key) || is_array($key));
        if (empty($this->name)) {
            $this->errors()->set($key, $value);
        } else {
            // если вложенный набор полей
            if (is_string($key)) {
                $key = "$this->name.$key";
                $this->errors()->set($key, $value);
            } else foreach ($key as $subkey => $subvalue) {
                $this->setError($subkey, $subvalue);
            }
        }
    }


    /**
     * Получение объекта ошибок.
     * @return Errors
     */
    public function errors()
    {
        return $this->errors;
    }

    /**
     * Валидация данных.
     * @param array маппинг значений [поле => значение]
     * @param bool|false проверять на все ошибки
     * @return bool
     */
    public function isValid($values, $multipleErrors = false): bool
    {
        assert(is_array($values) || is_object($values));
        if (is_object($values)) $values = (array) $values;
        $this->valuesBefore = &$values;
        $this->values = [];
        $this->errors()->clear();

        foreach ($this->fields as $name => &$field) {
            $this->extendsQuote($field, $name);

            $isset = isset($values[$name]);
            $value = $values[$name] ?? null;
            if ($field instanceof static) {
                // валидируем вложенный набор полей
                if (empty($value)) $value = [];
                if (false === $field->isValid($value, $multipleErrors)) {
                    $this->setError($field->errors()->map());
                    if (false === $multipleErrors) {
                        return false;
                    }
                }
                $this->values[$name] = $field->values;
            }
            else if ($field instanceof Field) {
                // валидируем поле
                if (false === $field->isValid($value, $isset)) {
                    $this->setError($name, $field->error);
                    if (false === $multipleErrors) {
                        return false;
                    }
                }
                if (empty($field->error) && !empty($field->same)) {
                    $sameValue = $values[$field->same] ?? null;
                    if ($sameValue !== $value) {
                        $field->setError($field->sameError);
                        $this->setError($name, $field->error);
                        if (false === $multipleErrors) {
                            return false;
                        }
                    }
                }
                $this->values[$name] = $field->value;
            }
        }
        if (!empty($this->errors()->has())) {
            return false;
        }
        return true;
    }

    /**
     * Проверка данных на валидность с выбрасом исключения в случае ошибки.
     * @param array|object маппинг значений [поле => значение]
     * @throws ValidateException
     */
    public function throwIfNotValid($values)
    {
        assert(is_array($values) || is_object($values));
        if (false === $this->isValid($values)) {
            throw new ValidateException($this->errors()->first());
        }
    }
}
