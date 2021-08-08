<?php
/**
 * Валидатор полного имени.
 * @package evas-php/evas-validate
 * @author Egor Vasyakin <egor@evas-php.com>
 */
namespace Evas\Validate\Fields;

use Evas\Validate\Field;

class FullNameField extends Field
{
    public $min = 2;
    public $max = 60;
    // public $pattern = '/^([a-zA-Zа-яА-Я]{2,} )+?$/u';
    // public $pattern = '/^[а-яёЁА-Я]*$/u';
    public $pattern = '/^([a-zA-Zа-яёЁА-Я]{2,} ?)+$/u';
}
