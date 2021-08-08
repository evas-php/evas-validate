<?php
/**
 * Валидатор логина.
 * @package evas-php/evas-validate
 * @author Egor Vasyakin <egor@evas-php.com>
 */
namespace Evas\Validate\Fields;

use Evas\Validate\Field;

class LoginField extends Field
{
    public $min = 4;
    public $max = 20;
    public $pattern = '/^[a-zA-Z_.-]+$/';
}
