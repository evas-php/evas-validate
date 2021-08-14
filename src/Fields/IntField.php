<?php
/**
 * Валидатор поля число int.
 * @package evas-php/evas-validate
 * @author Egor Vasyakin <egor@evas-php.com>
 */
namespace Evas\Validate\Fields;

use Evas\Validate\Field;

class IntField extends Field
{
    public $pattern = '/^\d+$/';
}
