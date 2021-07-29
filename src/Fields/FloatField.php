<?php
/**
 * Валидатор поля число float.
 * @package evas-php/evas-validate
 * @author Egor Vasyakin <egor@evas-php.com>
 */
namespace Evas\Validate\Fields;

use Evas\Validate\Field;

class FloatField extends Field
{
    public $type = ['string', 'float'];
    public $pattern = '/^\d+\.\d+$/';
}
