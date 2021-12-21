<?php
/**
 * Валидатор перечисления значений в json.
 * @package evas-php/evas-validate
 * @author Egor Vasyakin <egor@evas-php.com>
 */
namespace Evas\Validate\Fields;

use Evas\Validate\Field;

class JsonEnumField extends Field
{
    // public $pattern = '/^\[([^,]*,)*([^,]*)\]$/';
    public $pattern = '/^\[(\d+,)*(\d+)\]$/';

    /**
     * Хук после валидации поля.
     */
    protected function afterValidate()
    {
        $this->value = json_decode($this->value, true);
    }
}
