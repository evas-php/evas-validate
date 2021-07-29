<?php
/**
 * Валидатор поля дата.
 * @package evas-php/evas-validate
 * @author Egor Vasyakin <egor@evas-php.com>
 */
namespace Evas\Validate\Fields;

use Evas\Validate\Field;

class DateField extends Field
{
    public $pattern = '/^\d{4}-\d{2}-\d{2}$/';
}
