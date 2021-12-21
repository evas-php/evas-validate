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

    protected function prepareValue()
    {
        $pattern = '/\d/';
        preg_match_all($pattern, $this->value, $matches);
        $this->value = implode('', $matches[0]);
    }
}
