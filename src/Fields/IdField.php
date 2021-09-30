<?php

/**
 * Валидатор поля id.
 * @package evas-php/evas-validate
 * @author Egor Vasyakin <egor@evas-php.com>
 */
namespace Evas\Validate\Fields;

use Evas\Validate\Field;

class IdField extends Field
{
    public $pattern = '/^[1-9]+\d*$/';
}
