<?php
/**
 * Валидатор телефона.
 * @package evas-php/evas-validate
 * @author Egor Vasyakin <egor@evas-php.com>
 */
namespace Evas\Validate\Fields;

use Evas\Validate\Field;

class PhoneField extends Field
{
    public $min = 10;
    public $max = 16;
    public $pattern = '/(\d*)(\d{10})$/';

    /**
     * Подготовка значения к валидации.
     * @param mixed|null значение
     * @return mixed|null подготовленное значение
     */
    protected function prepareValue($value)
    {
        $pattern = '/\d/';
        preg_match_all($pattern, $value, $matches);
        return implode('', $matches[0]);
    }
}
