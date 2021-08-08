<?php
/**
 * Валидатор имени.
 * @package evas-php/evas-validate
 * @author Egor Vasyakin <egor@evas-php.com>
 */
namespace Evas\Validate\Fields;

use Evas\Validate\Field;

class NameField extends Field
{
    public $min = 2;
    public $max = 30;
    public $pattern = '/^[a-zA-Zа-яёЁА-Я]+$/u';
}
