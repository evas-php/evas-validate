<?php
/**
* @package evas-php/evas-validate
*/
namespace Evas\Validate;

use Evas\Validate\Field;

/**
* Валидация набора полей.
* @author Egor Vasyakin <e.vasyakin@itevas.ru>
*/
class Fieldset
{
    /**
    * @var array [имя поля => валидатор]
    */
    public $fields = [];

    /**
    * @var array  [имя поля => ошибка]
    */
    public $errors = [];

    /**
    * @var array [имя поля => значение]
    */
    public $values = [];

    /**
    * Конструктор.
    * @param array [имя поля => валидатор] или [имя поля => параметры валидатора]
    */
    public function __construct(array $params = null)
    {
        if ($params) foreach ($params as $name => $field) {
            $this->setField($name, $field);
        }
    }

    /**
    * Установка валидатора поля.
    * @param string имя поля
    * @param array|Field валидатор или параметры валидатора
    */
    public function setField(string $name, $field)
    {
        assert(is_array($field) || $field instanceof Field);
        if (is_array($field)) {
            $field = new Field($field);
        }
        if ($field instanceof Field) {
            $field->name = $name;
            $this->fields[$name] = $field;
        }
    }

    /**
    * Проверка значений на валидность списка полей.
    * @param array|object [поле => значение]
    * @param bool поддержка множественых ошибок
    * @throws \Exception если 1 аргумент не того типа
    * @return bool
    */
    public function isValid($values, $multipleErrors = false): bool
    {
        assert(is_array($values) || is_object($values));
        $this->errors = [];
        $this->values = [];
        foreach ($this->fields as $name => $field) {
            $isset = isset($values[$name]);
            $value = $values[$name] ?? null;

            if (false === $field->isValid($value, $isset)) {
                $this->errors[$name] = $field->error;
                if (false === $multipleErrors) {
                    return false;
                }
            }
            if (empty($field->error) && !empty($field->equal)) {
                $equalValue = $values[$field->equal] ?? null;
                if ($equalValue !== $value) {
                    $this->errors[$name] = $field->equalError ?? Field::ERROR_EQUAL . " in $field->name";
                    if (false === $multipleErrors) {
                        return false;
                    }
                }
            }
            $this->values[$name] = $value;
        }
        if (!empty($this->errors)) {
            return false;
        }
        return true;
    }
}
