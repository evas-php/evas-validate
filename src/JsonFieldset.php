<?php
/**
 * Валидатор набора полей в json.
 * @package evas-php\evas-validate
 * @author Egor Vasyakin <egor@evas-php.com>
 */
namespace Evas\Validate;

use Evas\Validate\Fieldset;

class JsonFieldset extends Fieldset
{
    /** @var string сообщение об ошибке разбора json */
    public $jsonParseError;
    /** @var string сообщение об ошибке пустого json */
    public $jsonEmptyError;
    /** @var string сообщение об ошибке типа json - должен быть строкой */
    public $jsonTypeError;

    /**
     * Подготовка значений к валидации.
     * @param mixed значения
     * @param bool|null проверять на все ошибки
     * @param bool|null вызывается ли из родительского набора полей
     * @return bool
     */
    public function isValid($values, bool $multipleErrors = false, bool $fromParent = false): bool
    {
        if (empty($values) && $values !== '0') {
            return $this->buildError('jsonEmpty');
        }
        if (!is_string($values)) {
            return $this->buildError('jsonType');
        }
        if (!is_array($values = @json_decode($values, true))) {
            return $this->buildError('jsonParse');
        }
        return parent::isValid($values, $multipleErrors, $fromParent);
    }
}
