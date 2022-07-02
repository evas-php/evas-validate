<?php
/**
 * Валидатор набора полей.
 * @package evas-php\evas-validate
 * @author Egor Vasyakin <egor@evas-php.com>
 */
namespace Evas\Validate;

use Evas\Base\Help\HooksTrait;
use Evas\Base\Help\PhpHelp;
use Evas\Validate\ErrorBuilder;
use Evas\Validate\Errors;
use Evas\Validate\HtmlEscapingTrait;
use Evas\Validate\Field;
use Evas\Validate\Interfaces\ValidatableInterface;
use Evas\Validate\ValidateException;

if (!defined('EVAS_VALIDATE_DEFAULT_FIELD_CLASS')) {
    define('EVAS_VALIDATE_DEFAULT_FIELD_CLASS', Field::class);
}

class Fieldset implements ValidatableInterface
{
    // подключаем поддержку произвольных хуков в наследуемых классах
    use HooksTrait;
    // Подключаем поддержкуэкранирования html-тегов.
    use HtmlEscapingTrait;

    /** @var string имя набора полей, требуется для вложенного набора полей */
    public $name;
    /** @var string шаблон ошибки несоответствия типа значений, переданных в набор полей */
    public $valuesTypeError;
    /** @var string класс валидатора поля по умолчанию */
    public $defaultFieldClass = EVAS_VALIDATE_DEFAULT_FIELD_CLASS;
    /** @var array исходные значения до валидации [имя поля => значение] */
    public $valuesBefore = [];
    /** @var array значения после валидации [имя поля => значение] */
    public $values = [];

    /** @var array валидаторы полей [имя поля => валидатор] */
    protected $fields = [];
    /** @var Errors объект ошибок */
    protected $errors;

    /**
     * Предустановленные поля валидатора.
     * @return array|null
     */
    public function presetFields(): ?array
    {
        return null;
    }

    /**
     * Предустановка свойств валидатора.
     */
    public function presetProps(): ?array
    {
        return null;
    }

    /**
     * Конструктор.
     * @param array|null маппинг валидаторов полей или параметров валидаторов полей по их именам
     * @param array|null параметры валидатора набора полей
     */
    public function __construct(array $fields = null, array $props = null)
    {
        $props = array_merge($this->presetProps() ?? [], $props ?? []);
        if (!empty($props)) $this->setProps($props);
        $fields = array_merge($this->presetFields() ?? [], $fields ?? []);
        if ($fields) $this->fields($fields);
        $this->hook('afterCreate');
    }

    /**
     * Установка свойств набора полей.
     * @param array маппинг свойств
     * @return self
     */
    public function setProps(array $props)
    {
        foreach ($props as $name => $value) {
            $this->$name = $value;
        }
        return $this;
    }

    /**
     * Установка класса валидатора поля по умолчанию.
     * @param string имя класса поля по умолчанию
     * @return self
     * @throws \InvalidArgumentException
     */
    public function defaultFieldClass(string $defaultFieldClass)
    {
        if (!is_subclass_of($defaultFieldClass, Field::class)) {
            throw new \InvalidArgumentException(sprintf(
                'Argument 1 passed to %s() must be an instance or a child of the %s, %s given', 
                __METHOD__, Field::class, PhpHelp::getType($defaultFieldClass)
            ));
        }
        $this->defaultFieldClass = $defaultFieldClass;
        return $this;
    }

    /**
     * Установка валидаторов полей.
     * @param array маппинг валидаторов полей или параметров валидаторов полей по их именам
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
     * @throws \InvalidArgumentException
     */
    public function field(string $name, $field)
    {
        if (is_array($field)) {
            $field = new $this->defaultFieldClass($field);
        }
        if (!($field instanceof Field || $field instanceof self)) {
            throw new \InvalidArgumentException(sprintf(
                'Argument 2 passed to %s() must be an array or an instance or a child of the %s or %s, %s given', 
                __METHOD__, Field::class, Fieldset::class, 
                PhpHelp::getType($field)
            ));
        }
        $field->name = $name;
        $this->fields[$name] = $field;
        return $this;
    }

    /**
     * Получение поля из набора полей.
     * @return ValidatableInterface|null вложенное поле или набор полей
     */
    public function getField(string $name): ?ValidatableInterface
    {
        return $this->fields[$name] ?? null;
    }

    /**
     * Получение всех полей из набора полей.
     * @return array
     */
    public function getFields(): array
    {
        return $this->fields;
    }

    /**
     * Получение объекта ошибок.
     * @return Errors
     */
    public function errors()
    {
        if (null === $this->errors) $this->errors = new Errors;
        return $this->errors;
    }

    /**
     * Установка ошибки/ошибок в список.
     * @param string|array ключ поля или маппинг ошибок по полям
     * @param string|null ошибка, если не маппинг
     * @throws \InvalidArgumentException
     */
    public function setError($key, string $value = null)
    {
        if (!is_string($key) && !is_array($key)) {
            throw new \InvalidArgumentException(sprintf(
                'Argument 1 passed to %s() must be a string or an array, %s given',
                __METHOD__, gettype($key)
            ));
        }
        if (empty($this->name)) {
            $this->errors()->set($key, $value);
        } else {
            // если вложенный набор полей
            if (is_string($key)) $key = [$key => $value];
            foreach ($key as $subkey => $subvalue) {
                $subkey = "$this->name.$subkey";
                $this->errors()->set($subkey, $subvalue);
            }
        }
    }

    /**
     * Сборка ошибки валидатора полей.
     * @param string тип ошибки
     * @return false
     */
    public function buildError(string $errorType)
    {
        $message = ErrorBuilder::build($errorType, $this);
        $this->errors()->set($this->name ?? '', $message);
        return false;
    }

    /**
     * Валидация данных.
     * @param array|object маппинг значений [поле => значение]
     * @param bool|null проверять на все ошибки
     * @param bool|null вызывается ли из родительского набора полей
     * @return bool
     * @throws \InvalidArgumentException
     */
    public function isValid($values, bool $multipleErrors = false, bool $fromParent = false): bool
    {
        if (!is_array($values) && !is_object($values) && !is_null($values)) {
            return $this->buildError('valuesType');
        }

        if (is_object($values)) $values = (array) $values;
        else if (is_null($values)) $values = [];
        $this->valuesBefore = &$values;
        $this->values = [];
        $this->errors()->clear();

        foreach ($this->fields as $name => &$field) {
            // наследуем экранирование html
            $this->inheritHtmlEscaping($field, $name);

            $isset = isset($values[$name]);
            $value = $values[$name] ?? null;
            if ($field instanceof Fieldset) {
                // валидируем вложенный набор полей
                if (empty($value)) $value = [];
                try {
                    if (false === $field->isValid($value, $multipleErrors, true)) {
                        $this->setError($field->errors()->map());
                        if (false === $multipleErrors) return false;
                    }
                } catch (\InvalidArgumentException $e) {
                    $this->setError($name, $e->getMessage());
                    if (false === $multipleErrors) return false;
                }
                $this->values[$name] = $field->values;
            }
            else if ($field instanceof Field) {
                // валидируем поле
                if (false === $field->isValid($value, $isset)) {
                    $this->setError($name, $field->error);
                    if (false === $multipleErrors) return false;
                }
                if (empty($field->error) && !empty($field->same)) {
                    $sameValue = $values[$field->same] ?? null;
                    if ($sameValue !== $value) {
                        $field->buildError('same');
                        $this->setError($name, $field->error);
                        if (false === $multipleErrors) return false;
                    }
                }
                $this->values[$name] = $field->value;
            }
        }
        if (!empty($this->errors()->has())) {
            return false;
        }
        $this->hook('afterValidate');
        return true;
    }

    /**
     * Проверка данных на валидность с выбрасом исключения в случае ошибки.
     * @param array|object маппинг значений [поле => значение]
     * @return self
     * @throws \InvalidArgumentException
     * @throws ValidateException
     */
    public function throwIfNotValid($values): ValidatableInterface
    {
        // if (!is_array($values) && !is_object($values)) {
        //     throw new \InvalidArgumentException(sprintf('Argument 1 $values must be an array or an object, %s given', gettype($key)));
        // }
        if (false === $this->isValid($values)) {
            throw new ValidateException($this->errors()->first());
        }
        return $this;
    }
}
