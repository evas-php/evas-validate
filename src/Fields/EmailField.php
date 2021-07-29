<?php
/**
 * Валидатор поля email.
 * @package evas-php/evas-validate
 * @author Egor Vasyakin <egor@evas-php.com>
 */
namespace Evas\Validate\Fields;

use Evas\Validate\Field;

class EmailField extends Field
{
    public $label = 'Email';
    public $min = 8;
    public $max = 60;
    public $pattern = '/^.{2,}@.{2,}\..{2,}$/';
}
